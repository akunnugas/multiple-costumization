export function submitEvent(formElement, buttonElement) {
    buttonElement.addEventListener("click", function (e) {
        e.preventDefault();
        formElement.submit();
    });
}

export function eventCheckFormValidity(formElement = null) {
    if (!formElement) {
        formElement = document.querySelector("form");
    }

    const buttonElements = document.querySelectorAll("button[type='submit']");
    
    const checkFormValidity = () => {
        if (formElement.checkValidity()) {
            buttonElements.forEach(function (buttonElement) {
                buttonElement.removeAttribute("disabled");
            });
        } else {
            buttonElements.forEach(function (buttonElement) {
                buttonElement.setAttribute("disabled", "disabled");
            });
        }
    }

    formElement.querySelectorAll('input, select, textarea').forEach(function (element) {
        element.addEventListener("input", checkFormValidity);
    });

    checkFormValidity();
}

export function eventWilayah(url=null) {
    
    const Level_negara = 0;
    const Level_provinsi = 1;
    const Level_kota = 2;
    
    const idNegara = $('[name="id_negara"]').closest('.form-group');
    const idProvinsi = $('[name="id_provinsi"]').closest('.form-group');
    const idKota = $('[name="id_kota"]').closest('.form-group');

    const selectNegara = $('.select-search[name="id_negara"]');
    const selectProvinsi = $('.select-search[name="id_provinsi"]');
    const selectKota = $('.select-search[name="id_kota"]');

    $('input[name="tingkat_mitra"]').on('change', function(){
        
        $(selectNegara).val('').trigger('change');
        $(selectProvinsi).val('').trigger('change');
        $(selectKota).val('').trigger('change');

        if($(this).val() === 'I'){
            idNegara.removeClass('d-none');
            idProvinsi.addClass('d-none');
            idKota.addClass('d-none');
        }else {
            idNegara.addClass('d-none');
            idProvinsi.removeClass('d-none');
            idKota.removeClass('d-none');
        }
    })
    
    $(selectProvinsi).on('change', function() {

        const provinsiId = $(this).val();
        const urlkota = url + '/wilayah/' + Level_kota + '/' + provinsiId;

        $(selectKota).empty().trigger('change');
        initSelect2(selectKota, urlkota, 'Kota / Kabupaten');
        console.log(urlkota);
    });

    function initSelect2(selector, url, placeholder) {
        $(selector).select2({
            theme: 'quantum3',
            placeholder: "Pilih " + placeholder,
            ajax: {
                url: url,
                dataType: 'json',
                processResults: function (data) {
                    return {
                        results: Object.entries(data).map(([key, value]) => ({
                            id: key,
                            text: value
                        }))
                    };
                },
                cache: true 
            }
        });
    }

}