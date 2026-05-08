@pushOnce('head')
    @vite('Modules/DMS/Resources/assets/sass/components/module-card.scss')
@endPushOnce

<div class="card module-card">
    <a href="{{url($module['path'])}}">
        <div class="card__body">
            <div class="module-card__icon">
                <img src="{{$module['icon']}}" alt="{{$module['label']}}" />
            </div>

            <h3 class="module-card__label">{{$module['label']}}</h3>
            <p class="module-card__file-count">{{$module['files']}} file</p>
        </div>
    </a>
</div>
