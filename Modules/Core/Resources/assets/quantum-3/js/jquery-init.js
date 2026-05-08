import $ from "jquery";
import select2 from "select2";
import "select2/dist/css/select2.min.css";

window.$ = $;
window.initSelect2 = select2

select2()

$('.select-search, .select-tree').select2({
    theme: 'quantum3',
});

$('.select-multiple').select2({
    theme: 'quantum3',
    multiple: true,
});

function formatNumber(value) {
    value = value.replace(/\D/g, ''); // Remove non-numeric characters
    return new Intl.NumberFormat('id-ID').format(value);
}

$('input[currency_field="currency_field"]').each((index, el) => {
    setTimeout(() => {
        el.value = formatNumber(el.value)
    })
})

$('input[currency_field="currency_field"]').on('keyup', (ev) => {
    ev.target.value = formatNumber(ev.target.value)
});
