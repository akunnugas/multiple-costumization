{{ $data['waktu_dipublikasi'] ? Carbon\Carbon::parse($data['waktu_dipublikasi'])->translatedFormat('d F Y') : '-' }}
