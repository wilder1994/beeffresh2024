/**
 * Dirección Colombia: mapa Google, cascada departamento/ciudad/barrio sin pisar la línea de dirección.
 */
function loadGoogleMaps(apiKey) {
    if (window.google?.maps) {
        return Promise.resolve();
    }

    return new Promise((resolve, reject) => {
        const script = document.createElement('script');
        script.src = `https://maps.googleapis.com/maps/api/js?key=${encodeURIComponent(apiKey)}&libraries=places&language=es&region=CO`;
        script.async = true;
        script.defer = true;
        script.onload = () => resolve();
        script.onerror = () => reject(new Error('No se pudo cargar Google Maps'));
        document.head.appendChild(script);
    });
}

function parseAddressComponents(components) {
    const out = { department: '', city: '', neighborhood: '' };

    for (const part of components) {
        const types = part.types || [];
        if (types.includes('administrative_area_level_1')) {
            out.department = normalizeDepartment(part.long_name);
        }
        if (types.includes('locality') || types.includes('administrative_area_level_2')) {
            if (!out.city) {
                out.city = part.long_name;
            }
        }
        if (types.includes('sublocality') || types.includes('sublocality_level_1') || types.includes('neighborhood')) {
            if (!out.neighborhood) {
                out.neighborhood = part.long_name;
            }
        }
    }

    if (out.department === '' && out.city === 'Bogotá') {
        out.department = 'Bogotá D.C.';
    }

    return out;
}

function normalizeDepartment(name) {
    if (!name) {
        return '';
    }
    const n = name.replace(/^Departamento de\s+/i, '').trim();
    if (/bogot/i.test(n)) {
        return 'Bogotá D.C.';
    }

    return n;
}

/** Inicializa pickers insertados por Livewire (solo nodos sin Alpine). */
export function bootAddressPickerNodes(root) {
    if (!window.Alpine || !root?.querySelectorAll) {
        return;
    }
    const nodes = root.matches?.('[data-bf-address-picker]')
        ? [root]
        : [...root.querySelectorAll('[data-bf-address-picker]')];
    nodes.forEach((node) => {
        if (!node._x_dataStack?.length) {
            window.Alpine.initTree(node);
        }
    });
}

export default function registerAddressPicker(Alpine) {
    Alpine.data('colombiaAddressPicker', (config) => ({
        apiKey: config.apiKey || '',
        locations: config.locations || window.__bfColombiaLocations || {},
        departments: config.departments || window.__bfColombiaDepartments || [],
        fieldIds: config.fieldIds || {},
        models: config.models || {},
        useLivewire: Boolean(config.useLivewire),

        department: config.initial?.department || '',
        city: config.initial?.city || '',
        neighborhood: config.initial?.neighborhood || '',
        address: config.initial?.address || '',
        latitude: config.initial?.latitude ?? '',
        longitude: config.initial?.longitude ?? '',

        cityOptions: [],
        neighborhoodOptions: [],
        neighborhoodIsSelect: false,
        mapOpen: false,
        mapLoading: false,
        mapError: null,
        mapInstance: null,
        mapMarker: null,
        mapAutocomplete: null,
        mapSearchError: null,
        mapBusy: false,
        _livewireTimer: null,
        _geoFromMap: false,

        init() {
            this.refreshCityOptions();
            this.refreshNeighborhoodOptions();
            this.$nextTick(() => this.pushToLivewire());
        },

        refreshCityOptions() {
            const entry = this.locations[this.department];
            this.cityOptions = entry?.cities ? [...entry.cities] : [];
            if (this.city && !this.cityOptions.includes(this.city)) {
                this.cityOptions.unshift(this.city);
            }
        },

        refreshNeighborhoodOptions() {
            const entry = this.locations[this.department];
            const list = entry?.neighborhoods?.[this.city] || [];
            this.neighborhoodOptions = [...list];
            this.neighborhoodIsSelect = this.neighborhoodOptions.length > 0;
            if (this.neighborhood && !this.neighborhoodOptions.includes(this.neighborhood)) {
                if (this.neighborhoodIsSelect) {
                    this.neighborhoodOptions.unshift(this.neighborhood);
                }
            }
        },

        onDepartmentChange() {
            const prevCity = this.city;
            this.refreshCityOptions();
            if (!this.cityOptions.includes(prevCity)) {
                this.city = this.cityOptions[0] || '';
                this.neighborhood = '';
            }
            this.refreshNeighborhoodOptions();
            this.scheduleLivewireSync();
        },

        onCityChange() {
            this.refreshNeighborhoodOptions();
            if (this.neighborhoodIsSelect && !this.neighborhoodOptions.includes(this.neighborhood)) {
                this.neighborhood = '';
            }
            this.scheduleLivewireSync();
        },

        onNeighborhoodChange() {
            this.scheduleLivewireSync();
        },

        onAddressInput() {
            this.scheduleLivewireSync();
        },

        scheduleLivewireSync() {
            if (this.mapOpen || !this.useLivewire) {
                return;
            }
            clearTimeout(this._livewireTimer);
            this._livewireTimer = setTimeout(() => this.pushToLivewire(), 120);
        },

        pushToLivewire() {
            if (!this.useLivewire || typeof Livewire === 'undefined') {
                return;
            }
            const root = this.$root.closest('[wire\\:id]');
            if (!root) {
                return;
            }
            const component = Livewire.find(root.getAttribute('wire:id'));
            if (!component) {
                return;
            }
            const m = this.models;
            if (m.address) {
                component.set(m.address, this.address, false);
            }
            if (m.department) {
                component.set(m.department, this.department, false);
            }
            if (m.city) {
                component.set(m.city, this.city, false);
            }
            if (m.neighborhood) {
                component.set(m.neighborhood, this.neighborhood, false);
            }
            if (m.latitude) {
                component.set(m.latitude, this.latitude === '' ? null : this.latitude, false);
            }
            if (m.longitude) {
                component.set(m.longitude, this.longitude === '' ? null : this.longitude, false);
            }
            if (m.country) {
                component.set(m.country, 'CO', false);
            }
        },

        applyGeoComponents(components, formattedAddress, lat, lng) {
            this._geoFromMap = true;
            this.address = formattedAddress || this.address;
            this.latitude = lat != null ? String(lat) : this.latitude;
            this.longitude = lng != null ? String(lng) : this.longitude;

            const parsed = parseAddressComponents(components || []);
            if (parsed.department) {
                this.department = parsed.department;
                this.refreshCityOptions();
            }
            if (parsed.city) {
                if (!this.cityOptions.includes(parsed.city)) {
                    this.cityOptions.unshift(parsed.city);
                }
                this.city = parsed.city;
            }
            this.refreshNeighborhoodOptions();
            if (parsed.neighborhood) {
                if (!this.neighborhoodIsSelect || this.neighborhoodOptions.includes(parsed.neighborhood)) {
                    this.neighborhood = parsed.neighborhood;
                } else if (this.neighborhoodIsSelect) {
                    this.neighborhoodOptions.unshift(parsed.neighborhood);
                    this.neighborhood = parsed.neighborhood;
                }
            }
        },

        async openMap() {
            if (!this.apiKey) {
                this.mapError = 'Falta GOOGLE_MAPS_API_KEY en el servidor.';
                this.mapOpen = true;
                return;
            }
            this.mapOpen = true;
            this.mapError = null;
            this.mapSearchError = null;
            this._geoFromMap = false;
            this.mapLoading = true;
            try {
                await loadGoogleMaps(this.apiKey);
                await this.$nextTick();
                this.initMap();
            } catch (e) {
                this.mapError = e?.message || 'Error al cargar el mapa';
            } finally {
                this.mapLoading = false;
            }
        },

        moveMapTo(lat, lng, zoom = 17) {
            if (!this.mapInstance || !this.mapMarker || !window.google?.maps) {
                return;
            }
            const pos = { lat, lng };
            this.mapMarker.setPosition(pos);
            this.mapInstance.panTo(pos);
            this.mapInstance.setZoom(zoom);
        },

        searchMapAddress() {
            if (this.mapBusy) {
                return Promise.resolve(false);
            }

            const searchEl = this.$refs.mapSearch;
            const query = searchEl?.value?.trim();
            if (!query) {
                this.mapSearchError = 'Escribe una dirección para buscar.';
                return Promise.resolve(false);
            }
            if (!window.google?.maps) {
                return Promise.resolve(false);
            }

            this.mapSearchError = null;
            this.mapBusy = true;

            return new Promise((resolve) => {
                const geocoder = new google.maps.Geocoder();
                geocoder.geocode(
                    { address: query, componentRestrictions: { country: 'co' } },
                    (results, status) => {
                        this.mapBusy = false;
                        if (status !== 'OK' || !results?.[0]?.geometry?.location) {
                            this.mapSearchError =
                                'No encontramos esa dirección. Elige una sugerencia de Google (debajo del campo) o haz clic en el mapa.';
                            resolve(false);
                            return;
                        }

                        const r = results[0];
                        const loc = r.geometry.location;
                        const lat = loc.lat();
                        const lng = loc.lng();

                        this.moveMapTo(lat, lng);
                        this.applyGeoComponents(r.address_components, r.formatted_address, lat, lng);

                        if (searchEl && r.formatted_address) {
                            searchEl.value = r.formatted_address;
                        }

                        resolve(true);
                    }
                );
            });
        },

        initMap() {
            const mapEl = this.$refs.mapCanvas;
            const searchEl = this.$refs.mapSearch;
            if (!mapEl || !window.google?.maps) {
                return;
            }

            mapEl.innerHTML = '';

            const lat = parseFloat(this.latitude) || 4.711;
            const lng = parseFloat(this.longitude) || -74.0721;
            const center = { lat, lng };

            if (searchEl) {
                searchEl.value = this.address || '';
            }

            this.mapInstance = new google.maps.Map(mapEl, {
                center,
                zoom: this.latitude ? 16 : 6,
                mapTypeControl: false,
                streetViewControl: false,
                fullscreenControl: true,
            });

            this.mapMarker = new google.maps.Marker({
                map: this.mapInstance,
                position: center,
                draggable: true,
            });

            requestAnimationFrame(() => {
                if (!this.mapInstance) {
                    return;
                }
                google.maps.event.trigger(this.mapInstance, 'resize');
                this.mapInstance.setCenter(center);
            });

            this.mapMarker.addListener('dragend', () => {
                const pos = this.mapMarker.getPosition();
                if (pos) {
                    this.reverseGeocode(pos.lat(), pos.lng());
                }
            });

            if (searchEl) {
                this.mapAutocomplete = new google.maps.places.Autocomplete(searchEl, {
                    componentRestrictions: { country: 'co' },
                    fields: ['formatted_address', 'geometry', 'address_components'],
                });
                this.mapAutocomplete.addListener('place_changed', () => {
                    const place = this.mapAutocomplete.getPlace();
                    if (!place?.geometry?.location) {
                        this.mapSearchError =
                            'Elige una sugerencia de Google (no la del navegador) o pulsa Buscar.';
                        return;
                    }
                    const loc = place.geometry.location;
                    const placeLat = loc.lat();
                    const placeLng = loc.lng();
                    this.mapSearchError = null;
                    this.moveMapTo(placeLat, placeLng);
                    this.applyGeoComponents(
                        place.address_components,
                        place.formatted_address,
                        placeLat,
                        placeLng
                    );
                    if (place.formatted_address) {
                        searchEl.value = place.formatted_address;
                    }
                });
            }

            this.mapInstance.addListener('click', (e) => {
                const clickLat = e.latLng.lat();
                const clickLng = e.latLng.lng();
                this.mapMarker.setPosition(e.latLng);
                this.reverseGeocode(clickLat, clickLng);
            });
        },

        reverseGeocode(lat, lng) {
            if (!window.google?.maps || this.mapBusy) {
                return;
            }
            this.mapBusy = true;
            const geocoder = new google.maps.Geocoder();
            geocoder.geocode({ location: { lat, lng } }, (results, status) => {
                this.mapBusy = false;
                if (status !== 'OK' || !results?.[0]) {
                    return;
                }
                const r = results[0];
                this.applyGeoComponents(r.address_components, r.formatted_address, lat, lng);
                const searchEl = this.$refs.mapSearch;
                if (searchEl && r.formatted_address) {
                    searchEl.value = r.formatted_address;
                }
            });
        },

        async confirmMap() {
            const searchEl = this.$refs.mapSearch;
            const query = searchEl?.value?.trim();
            if (query && !this._geoFromMap && window.google?.maps) {
                await this.searchMapAddress();
            }
            this.mapOpen = false;
            this.destroyMap();
            this.pushToLivewire();
        },

        closeMap() {
            this.mapOpen = false;
            this.destroyMap();
        },

        destroyMap() {
            if (this.mapAutocomplete) {
                google.maps.event.clearInstanceListeners(this.mapAutocomplete);
                this.mapAutocomplete = null;
            }
            if (this.mapMarker) {
                google.maps.event.clearInstanceListeners(this.mapMarker);
                this.mapMarker.setMap(null);
                this.mapMarker = null;
            }
            if (this.mapInstance) {
                google.maps.event.clearInstanceListeners(this.mapInstance);
                this.mapInstance = null;
            }
            if (this.$refs.mapCanvas) {
                this.$refs.mapCanvas.innerHTML = '';
            }
        },
    }));
}
