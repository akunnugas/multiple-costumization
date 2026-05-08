<div style="position: fixed; bottom: 3.2rem; right: 3.2rem">
    <button type="button" class="btn  btn_primary btn_xs" id="scroll_top">
        <span class="icon icon-chevron-double-up"></span>
        Kembali Keatas
    </button>
</div>

@push('scripts')
    <script>
        document.querySelector('#scroll_top').addEventListener('click', () => {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
    </script>
@endpush
