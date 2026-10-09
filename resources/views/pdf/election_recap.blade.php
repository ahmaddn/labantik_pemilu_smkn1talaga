<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Berita Acara Rekapitulasi - {{ $election->title }}</title>
    <style>
        @page {
            margin: 1.2cm 1.8cm 1.8cm 1.8cm;
            size: A4 portrait;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 10pt;
            line-height: 1.35;
            color: #000;
        }
        
        /* ── Kop Surat Standar SMKN 1 Talaga (Sesuai SIMS) ── */
        .kop-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 0px;
        }
        .kop-table td {
            padding: 0;
            border: none;
        }
        .kop-divider {
            border-top: 3px solid #000;
            border-bottom: 1px solid #000;
            height: 2px;
            margin: 6px 0 14px 0;
        }
        
        /* ── Judul Dokumen ── */
        .title-doc {
            text-align: center;
            margin-bottom: 14px;
        }
        .title-doc h1 {
            font-size: 13pt;
            font-weight: bold;
            text-transform: uppercase;
            margin: 0;
            text-decoration: underline;
            letter-spacing: 0.5px;
        }
        .title-doc p {
            font-size: 9.5pt;
            margin: 3px 0 0 0;
            color: #000;
            font-weight: normal;
        }

        /* ── Narasi ── */
        .narration {
            font-size: 9.5pt;
            text-align: justify;
            margin-bottom: 12px;
        }

        /* ── Tabel Informasi Acara ── */
        .table-meta {
            width: 100%;
            margin-bottom: 12px;
            font-size: 9.5pt;
            border-collapse: collapse;
        }
        .table-meta td {
            padding: 2.5px 4px;
            vertical-align: top;
            border: none;
        }
        .table-meta td.label {
            width: 28%;
            font-weight: bold;
        }
        .table-meta td.separator {
            width: 2%;
            text-align: center;
        }

        /* ── Ringkasan Partisipasi ── */
        .stats-box {
            width: 100%;
            border: 1px solid #000;
            background-color: #f8fafc;
            border-collapse: collapse;
            margin-bottom: 14px;
            font-size: 9pt;
        }
        .stats-box td {
            padding: 6px 8px;
            text-align: center;
            border: 1px solid #000;
        }
        .stats-box strong {
            display: block;
            font-size: 12pt;
            color: #000;
            margin-top: 2px;
        }

        /* ── Tabel Hasil Perolehan Suara ── */
        .table-result {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
            margin-bottom: 14px;
            font-size: 9.5pt;
        }
        .table-result th {
            background-color: #f2f2f2;
            border: 1px solid #000;
            padding: 6px 4px;
            text-align: center;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 8.5pt;
        }
        .table-result td {
            border: 1px solid #000;
            padding: 5px 6px;
            vertical-align: middle;
        }
        .text-center {
            text-align: center;
        }
        .font-bold {
            font-weight: bold;
        }
        .badge {
            display: inline-block;
            padding: 1px 4px;
            font-size: 8pt;
            border-radius: 3px;
            background-color: #e2e8f0;
            color: #0f172a;
            font-weight: bold;
        }
    </style>
</head>
<body>
    @php
        $logoPath = public_path('assets/media/logos/pemprov.png');
        $logoBase64 = file_exists($logoPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath)) : null;
    @endphp

    {{-- KOP SURAT STANDAR SMKN 1 TALAGA --}}
    <table cellspacing="0" cellpadding="0" class="kop-table">
        <tr>
            <td style="width:100px; vertical-align:middle; text-align:center; padding-right:8px;">
                @if($logoBase64)
                    <img src="{{ $logoBase64 }}" width="85" height="100" alt="Logo Pemprov Jabar" />
                @endif
            </td>
            <td style="vertical-align:top; text-align:center; padding-left:2px;">
                <p style="margin:0 0 1px; font-size:11pt; font-family:Arial; font-weight:bold;">PEMERINTAH DAERAH PROVINSI JAWA BARAT</p>
                <p style="margin:0 0 1px; font-size:14pt; font-family:Arial; font-weight:bold; line-height:1.2;">CABANG DINAS PENDIDIKAN WILAYAH IX</p>
                <p style="margin:0 0 2px; font-size:12pt; font-family:Arial; font-weight:bold;">SEKOLAH MENENGAH KEJURUAN NEGERI 1 TALAGA</p>
                <p style="margin:0 0 1px; font-size:8.5pt; font-family:Tahoma;">Bidang Keahlian: Teknologi dan Rekayasa, Teknologi Informasi komunikasi, Bisnis dan Manajemen</p>
                <p style="margin:0 0 1px; font-size:8.5pt; font-family:Tahoma;">Kampus 1: Jalan Sekolah Nomor 20 Desa Talagakulon Kecamatan Talaga Kabupaten Majalengka</p>
                <p style="margin:0 0 1px; font-size:8.5pt; font-family:Tahoma;">Kampus 2: Jalan Talaga-Bantarujeg Desa Mekarrahaja Kecamatan Talaga Kabupaten Majalengka</p>
                <p style="margin:0 0 1px; font-size:8.5pt; font-family:Tahoma;">Telpon (0233) 319238 FAX (0233) 319238 POS 45463 NPSN: 20213872</p>
            </td>
        </tr>
    </table>
    <div class="kop-divider"></div>

    {{-- JUDUL DOKUMEN --}}
    <div class="title-doc">
        <h1>REKAPITULASI HASIL PEROLEHAN SUARA</h1>
        <p>Sistem Pemilihan Elektronik (E-Voting) SMKN 1 Talaga</p>
    </div>

    {{-- NARASI PENGANTAR --}}
    <div class="narration">
        Berikut merupakan data rekapitulasi penghitungan perolehan suara pemilihan secara elektronik (E-Voting) yang dilaksanakan pada hari <strong>{{ $generatedDay }}</strong> tanggal <strong>{{ $generatedDate }}</strong> melalui sistem Pemilu Digital SMKN 1 Talaga:
    </div>

    {{-- INFORMASI ACARA --}}
    <table class="table-meta">
        <tr>
            <td class="label">Nama Pemilihan</td>
            <td class="separator">:</td>
            <td class="font-bold">{{ $election->title }}</td>
        </tr>
        <tr>
            <td class="label">Jenis / Kategori</td>
            <td class="separator">:</td>
            <td>{{ strtoupper($election->type) }}</td>
        </tr>
        <tr>
            <td class="label">Tahun Ajaran</td>
            <td class="separator">:</td>
            <td>{{ $election->academic_year ?? 'Semua Angkatan' }}</td>
        </tr>
        <tr>
            <td class="label">Tahapan Pemilihan</td>
            <td class="separator">:</td>
            <td>{{ $election->is_multi_stage ? "Tahap {$selectedStage} dari {$election->total_stages} Tahap" : 'Pemilihan Tunggal (1 Putaran)' }}</td>
        </tr>
        <tr>
            <td class="label">Waktu Rekapitulasi</td>
            <td class="separator">:</td>
            <td>{{ $generatedTimestamp }} WIB</td>
        </tr>
    </table>

    {{-- RINGKASAN SUARA & PARTISIPASI --}}
    <table class="stats-box">
        <tr>
            <td>
                <span>Total Hak Pilih Terdaftar</span>
                <strong>{{ number_format($totalVoters, 0, ',', '.') }}</strong>
            </td>
            <td>
                <span>Pemilih Hadir / Mencoblos</span>
                <strong>{{ number_format($totalVotedUsers, 0, ',', '.') }} Pemilih</strong>
            </td>
            <td>
                <span>Total Suara Masuk</span>
                <strong>{{ number_format($totalVotes, 0, ',', '.') }} Suara</strong>
                @if(isset($maxVotes) && $maxVotes > 1)
                    <small style="font-size: 7.5pt; color: #64748b; display: block;">(Maks. {{ $maxVotes }} suara/pemilih)</small>
                @endif
            </td>
            <td>
                <span>Tingkat Partisipasi</span>
                <strong>{{ $turnoutPercentage }}%</strong>
            </td>
            <td>
                <span>Status Pemilihan</span>
                <strong style="font-size: 10pt; text-transform: uppercase;">{{ $election->status === 'closed' ? 'Selesai' : 'Berjalan' }}</strong>
            </td>
        </tr>
    </table>

    {{-- TABEL HASIL PEROLEHAN SUARA --}}
    @php
        $effectiveMaxVotes = $maxVotes ?? ($election->max_votes_per_voter ?? 1);
        $isFinalOrSingle = !$election->is_multi_stage || $selectedStage >= $election->total_stages;
    @endphp
    <table class="table-result">
        <thead>
            <tr>
                <th style="width: 7%;">No.</th>
                <th style="width: 48%; text-align: left;">Nama Calon Paslon / Rombel</th>
                <th style="width: 17%;">Jumlah Suara</th>
                <th style="width: 14%;">Persentase</th>
                <th style="width: 14%;">Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($results as $index => $cand)
            <tr>
                <td class="text-center font-bold">{{ $cand['candidate_number'] }}</td>
                <td>
                    <div class="font-bold">{{ $cand['chairman_name'] }}</div>
                    @if(!empty($cand['chairman_class']))
                        <span class="badge">{{ $cand['chairman_class'] }}</span>
                    @endif
                    @if(!empty($cand['vice_chairman_name']))
                        <div style="font-size: 8.5pt; color: #475569; margin-top: 2px;">
                            Wakil: {{ $cand['vice_chairman_name'] }}
                            @if(!empty($cand['vice_chairman_class']))
                                <span class="badge">{{ $cand['vice_chairman_class'] }}</span>
                            @endif
                        </div>
                    @endif
                </td>
                <td class="text-center font-bold" style="font-size: 10.5pt;">
                    {{ number_format($cand['votes_count'], 0, ',', '.') }}
                </td>
                <td class="text-center font-bold">
                    {{ $cand['percentage'] }}%
                </td>
                <td class="text-center" style="font-size: 8.5pt;">
                    @if(isset($cand['is_qualified']) && $cand['is_qualified'] === false)
                        <span style="color: #b91c1c;">Gugur (Tahap {{ $cand['eliminated_at_stage'] ?? 1 }})</span>
                    @elseif($cand['votes_count'] > 0 && $index < $effectiveMaxVotes)
                        @if($isFinalOrSingle)
                            <strong style="color: #0369a1;">JUARA {{ $index + 1 }} (TERPILIH)</strong>
                        @else
                            <strong style="color: #0369a1;">LOLOS (PERINGKAT {{ $index + 1 }})</strong>
                        @endif
                    @else
                        <span>Memenuhi Syarat</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="text-center" style="color: #64748b; padding: 16px;">
                    Belum ada data calon atau suara yang masuk.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="narration" style="font-size: 9pt; margin-top: 14px; color: #334155;">
        Dokumen Rekapitulasi Hasil Perolehan Suara Elektronik ini diterbitkan secara otomatis melalui sistem Pemilu Digital SMKN 1 Talaga pada tanggal {{ $generatedDate }} pukul {{ $generatedTimestamp }} WIB dan sah sebagai arsip resmi hasil pemilihan.
    </div>
</body>
</html>
