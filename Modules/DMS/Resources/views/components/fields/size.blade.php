<p>{{
    isset($data['ukuran'])
        ? \Modules\Core\Helpers\Format::formatBytes($data['ukuran'])
        : '—'
    }}
</p>
