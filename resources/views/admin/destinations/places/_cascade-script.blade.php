<script>
(function () {
    var countrySel = document.getElementById('countrySelect');
    var stateSel = document.getElementById('stateSelect');
    var citySel = document.getElementById('citySelect');
    if (!countrySel || !stateSel || !citySel) {
        return;
    }
    var statesUrl = countrySel.dataset.statesUrl;
    var citiesUrl = stateSel.dataset.citiesUrl;
    var presetState = stateSel.dataset.selected || '';
    var presetCity = citySel.dataset.selected || '';

    function fillOptions(select, items, placeholder) {
        select.innerHTML = '';
        var opt = document.createElement('option');
        opt.value = '';
        opt.textContent = placeholder;
        select.appendChild(opt);
        items.forEach(function (item) {
            var o = document.createElement('option');
            o.value = item.id;
            o.textContent = item.name;
            select.appendChild(o);
        });
        select.disabled = items.length === 0;
    }

    function loadStates(countryId, selectedStateId, callback) {
        if (!countryId) {
            fillOptions(stateSel, [], '-- Select State --');
            fillOptions(citySel, [], '-- Select City --');
            if (callback) {
                callback();
            }
            return;
        }
        stateSel.disabled = true;
        fetch(statesUrl.replace('__COUNTRY__', countryId))
            .then(function (r) { return r.json(); })
            .then(function (items) {
                fillOptions(stateSel, items, '-- Select State --');
                if (selectedStateId) {
                    stateSel.value = selectedStateId;
                }
                if (callback) {
                    callback();
                }
            });
    }

    function loadCities(stateId, selectedCityId) {
        if (!stateId) {
            fillOptions(citySel, [], '-- Select City --');
            return;
        }
        citySel.disabled = true;
        fetch(citiesUrl.replace('__STATE__', stateId))
            .then(function (r) { return r.json(); })
            .then(function (items) {
                fillOptions(citySel, items, '-- Select City --');
                if (selectedCityId) {
                    citySel.value = selectedCityId;
                }
            });
    }

    countrySel.addEventListener('change', function () {
        loadStates(countrySel.value, '', null);
    });
    stateSel.addEventListener('change', function () {
        loadCities(stateSel.value, '');
    });

    if (countrySel.value) {
        loadStates(countrySel.value, presetState, function () {
            if (stateSel.value) {
                loadCities(stateSel.value, presetCity);
            }
        });
    }
})();
</script>
