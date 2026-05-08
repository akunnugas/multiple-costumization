@php
    $user = auth()->user();

    // get id livechat
    $freshchatTags = session('token.id_livechat');

    // define hours
    $now = now()->setTimezone('Asia/Jakarta');
    [$isBeforeOfficeHour, $isBreakHour, $isOutsideOfficeHours] = [
        $now->lt($now->copy()->setTime(8,30)),
        $now->between($now->copy()->setTime(12,0), $now->copy()->setTime(13,0), false),
        $now->gt($now->copy()->setTime(17,30)),
    ];
    $isWeekend = $now->isWeekend();

    $isLiveChatIdEmpty = empty($freshchatTags);
    if ($isLiveChatIdEmpty || $isWeekend || $isOutsideOfficeHours || $isBreakHour || $isBeforeOfficeHour) {
        $freshchatTags = ''; // ID Livechat All Team
    }

    $kodeUniv = session('token.kode_univ');
    $namaUniv = session('token.nama_univ');
    $loginAs = session('token.login_as');
    $freshchatRestoreId = session('token.restore_id') ?? null;

    $loginAsLabel = '';
    if (!empty($loginAs) && is_array($loginAs)) {
        $loginAsName = $loginAs['nama'] ?? $loginAs['name'] ?? $loginAs['username'] ?? null;
        $loginAsEmail = $loginAs['email'] ?? null;

        if (!empty($loginAsName) || !empty($loginAsEmail)) {
            $loginAsLabel = 'Login as ' . trim(($loginAsName ?? '-') . (!empty($loginAsEmail) ? ' (' . $loginAsEmail . ')' : ''));
        }
    }
@endphp

<script>
    const externalId = @json($user->email_user);
    const restoreIdKey = `fc_restore_id_${externalId}`;
    const restoreIdFromSession = @json($freshchatRestoreId);
    const restoreId = restoreIdFromSession || localStorage.getItem(restoreIdKey) || null;

    window.fcSettings = {
        onInit: function() {
            window.fcWidget.conversation.setConversationProperties({
                cf_username: "{{ $user->email_user }}",
                cf_university_code: "{{ $kodeUniv }}",
                cf_university_name: "{{ $namaUniv }}",
                cf_application_name: "V2",
                cf_module_name: "{{ $user->nama_modul }}",
                cf_role_name: "{{ $user->nama_role }}",
                cf_livechat_id: "{{ $freshchatTags }}",
                cf_loginas: @json($loginAsLabel),
            });

            window.fcWidget.on("widget:opened", function() {
                localStorage.removeItem('fc_widget_close');
                localStorage.setItem('fc_widget_opened', 'true');
            });
            window.fcWidget.on("widget:closed", function() {
                localStorage.removeItem('fc_widget_opened');
                localStorage.setItem('fc_widget_close', 'true');
            });
        }
    };
    window.fcWidgetMessengerConfig = {
        firstName: @json($user->nama_user),
        email: @json($user->email_user),
        externalId: externalId,
        restoreId: restoreId,
        tags: ["{{ $freshchatTags }}"],
        open: localStorage.getItem('fc_widget_opened') === 'true',
        locale: "id",
        config: {
            cssNames: {
                expanded: "custom_fc_expanded",
                widget: "custom_fc_frame",
            },
        },
        meta: {
            cf_username: "{{ $user->email_user }}",
            cf_university_code: "{{ $kodeUniv }}",
            cf_university_name: "{{ $namaUniv }}",
            cf_role: "{{ $user->nama_role }}",
            cf_loginas: @json($loginAsLabel),
        },
    }
</script>
<script src='//fw-cdn.com/13461226/5531285.js' chat='true'></script>
