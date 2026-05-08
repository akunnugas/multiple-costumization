<div>
    {{-- Breadcrumb --}}
    <x-admission::breadcrumb />

    <section id="announcements">
        <div class="container">
            <div class="custom-center">
                <div class="grid">
                    <div class="col-12">
                        <div class="card card-announcements">
                            <div class="detail">
                                <div class="heading">
                                    <p class="date">
                                        {{ __('admission::announcement.posted_by') }} {{ $new['created_by'] }} • {{ $new['created_at'] }}
                                    </p>
                                    <h1 class="title">{{ $new['title'] }}</h1>
                                </div>
                                <div class="thumbnail">
                                    <img loading="lazy" src="{{ $new['thumbnail'] }}" alt="thumbnail" style="object-fit: contain;">
                                </div>
                                <div class="content">
                                    {!! $new['information'] !!}

                                    @if(!empty($newFiles))
                                        <p><b>{{ __('admission::announcement.attachments') }}: </b></p>

                                        <ul style="padding-left: 2rem;">
                                            @foreach($newFiles as $file)
                                                <li>
                                                    <a class="link" download="{{ $file['name'] }}" href="{{ $file['url'] }}">
                                                        {{ $file['name'] }}
                                                    </a>
                                                </li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </div>
                                <div class="social-share">
                                    <p>{{ __('admission::announcement.share') }} : </p>
                                    <a target="_blank" href="http://twitter.com/share?url={{ url()->current() }}">
                                        <div class="circle-social">
                                            <img src="{{ Page::quantumAsset('images/misc-icons/social-networks/twitter.svg') }}" alt="Twitter Logo">
                                        </div>
                                    </a>
                                    <a target="_blank" href="https://www.facebook.com/sharer/sharer.php?u={{ url()->current() }}">
                                        <div class="circle-social">
                                            <img src="{{ Page::quantumAsset('images/misc-icons/social-networks/facebook.svg') }}" alt="Facebook Logo">
                                        </div>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
