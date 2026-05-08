@props([
    'container' => false
])

<footer class="footer">
    @if($container)
    <div class="container">
    @endif
    <div class="footer__copyright-wrapper">
        <div class="footer__logo-wrapper">
            <img src="{{ Page::quantumAsset('images/logos/sevima.png') }}" alt="" class="footer__logo">
        </div>
        <p class="footer__copyright">
            © 2005-{{ date('Y') }} SEVIMA. All Rights Reserved
        </p>
    </div>
    <p class="footer__brand-name">
        SEVIMA Platform
    </p>
    @if($container)
    </div>
    @endif
</footer>
