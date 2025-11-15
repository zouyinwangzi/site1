jQuery(document).ready(function($) {
    function initializeContinentCountryDropdown() {
        const continentSelect = $('#form-field-continent');
        const countrySelect = $('#form-field-country');
        
        if (continentSelect.length && countrySelect.length && typeof continentsCountriesData !== 'undefined') {
            continentSelect.on('change', function() {
                const continent = $(this).val();


                console.log('Selected continent:', continent);
                console.log(continentsCountriesData);
                
                if (continent && continentsCountriesData[continent]) {
                    const countries = continentsCountriesData[continent].countries;

                    console.log('Countries for selected continent:', countries);

                    countrySelect.html('<option value="">Select Country/Region</option>');
                    
                    countries.forEach(function(country) {
                        countrySelect.append(
                            $('<option></option>').val(country).text(country)
                        );
                    });
                } else {
                    countrySelect.html('<option value="">Select the country of your whatsapp.</option>');
                }
            });
            
            countrySelect.html('<option value="">Select the country of your whatsapp.</option>');
        }
    }
    
    // 初始化
    $(document).on('elementor/popup/show', initializeContinentCountryDropdown);
    setTimeout(initializeContinentCountryDropdown, 1000);
});