<?php

declare(strict_types=1);

namespace App\Services;

class P5PresetService
{
    /**
     * Daftar Tema Standar Projek P5 & P5RA (Kemendikbud & Kemenag).
     *
     * @return array<int, string>
     */
    public function getThemes(): array
    {
        return [
            'Gaya Hidup Berkelanjutan',
            'Kearifan Lokal',
            'Bhinneka Tunggal Ika',
            'Bangunlah Jiwa dan Raganya',
            'Suara Demokrasi',
            'Rekayasa dan Teknologi',
            'Kewirausahaan',
            'Kebekerjaan',
        ];
    }

    /**
     * Daftar 6 Dimensi Profil Pelajar Pancasila beserta Elemen & Sub-elemen standar.
     *
     * @return array<string, array{elements: array<string, array<int, string>>}>
     */
    public function getPancasilaDimensions(): array
    {
        return [
            'Beriman, Bertakwa Kepada Tuhan YME, dan Berakhlak Mulia' => [
                'elements' => [
                    'Akhlak Beragama' => [
                        'Mengenal dan Mencintai Tuhan Yang Maha Esa',
                        'Pemahaman Agama / Kepercayaan',
                        'Pelaksanaan Ritual Ibadah',
                    ],
                    'Akhlak Pribadi' => [
                        'Integritas dan Kejujuran',
                        'Merawat Diri secara Fisik, Mental, dan Spiritual',
                    ],
                    'Akhlak Kepada Manusia' => [
                        'Mengutamakan Persamaan dengan Orang Lain dan Menghargai Perbedaan',
                        'Berempati kepada Orang Lain',
                    ],
                    'Akhlak Kepada Alam' => [
                        'Menjaga Lingkungan Alam Sekitar',
                        'Memahami Keterhubungan Ekosistem Bumi',
                    ],
                    'Akhlak Bernegara' => [
                        'Melaksanakan Hak dan Kewajiban sebagai Warga Negara Indonesia',
                    ],
                ],
            ],
            'Berkebhinekaan Global' => [
                'elements' => [
                    'Mengenal dan Menghargai Budaya' => [
                        'Mendalami Budaya dan Identitas Budaya',
                        'Mengeksplorasi dan Membandingkan Pengetahuan Budaya',
                        'Menumbuhkan Rasa Menghormati terhadap Keanekaragaman Budaya',
                    ],
                    'Komunikasi dan Interaksi Antar Budaya' => [
                        'Berkomunikasi Antar Budaya dengan Empati',
                        'Mempertimbangkan dan Menumbuhkan Berbagai Perspektif',
                    ],
                    'Refleksi dan Tanggung Jawab terhadap Pengalaman Kebhinekaan' => [
                        'Refleksi terhadap Pengalaman Kebhinekaan',
                        'Menghilangkan Stereotip dan Prasangka',
                        'Menyelaraskan Perbedaan Budaya',
                    ],
                    'Berkeadilan Sosial' => [
                        'Aktif Membangun Masyarakat yang Inklusif, Adil, dan Berkelanjutan',
                        'Berpartisipasi dalam Proses Pengambilan Keputusan Bersama',
                    ],
                ],
            ],
            'Gotong Royong' => [
                'elements' => [
                    'Kolaborasi' => [
                        'Kerja Sama dan Koordinasi Positif',
                        'Komunikasi untuk Mencapai Tujuan Bersama',
                        'Saling Ketergantungan Positif',
                    ],
                    'Kepedulian' => [
                        'Tanggap terhadap Lingkungan Sosial',
                        'Persepsi Sosial yang Positif',
                    ],
                    'Berbagi' => [
                        'Memberi dan Menerima Segala Hal yang Berharga bagi Kehidupan',
                    ],
                ],
            ],
            'Mandiri' => [
                'elements' => [
                    'Pemahaman Diri dan Situasi yang Dihadapi' => [
                        'Mengenali Kualitas dan Minat Diri serta Tantangan yang Dihadapi',
                        'Mengembangkan Refleksi Diri',
                    ],
                    'Regulasi Diri' => [
                        'Regulasi Emosi',
                        'Penetapan Tujuan Belajar dan Rencana Strategis',
                        'Menunjukkan Inisiatif dan Bekerja secara Mandiri',
                        'Percaya Diri, Tangguh, dan Adaptif',
                    ],
                ],
            ],
            'Bernalar Kritis' => [
                'elements' => [
                    'Memperoleh dan Memproses Informasi dan Gagasan' => [
                        'Mengajukan Pertanyaan Kritis',
                        'Mengidentifikasi, Mengklarifikasi, dan Mengolah Informasi',
                    ],
                    'Menganalisis dan Mengevaluasi Penalaran' => [
                        'Menganalisis Bukti dan Argumen',
                        'Menjelaskan Alasan untuk Mendukung Pemikiran',
                    ],
                    'Refleksi Pemikiran dan Proses Berpikir' => [
                        'Merefleksi dan Mengevaluasi Pemikirannya Sendiri',
                    ],
                ],
            ],
            'Kreatif' => [
                'elements' => [
                    'Menghasilkan Gagasan yang Orisinal' => [
                        'Menggabungkan Gagasan Baru dan Imajinatif',
                        'Mengekspresikan Pikiran dan Perasaan dalam Bentuk Karya',
                    ],
                    'Menghasilkan Karya dan Tindakan yang Orisinal' => [
                        'Mengeksplorasi dan Mengekspresikan Ide Kreatif',
                    ],
                    'Memiliki Keluwesan Berpikir dalam Mencari Alternatif Solusi' => [
                        'Menentukan Pilihan Solusi Kreatif saat Menghadapi Masalah',
                    ],
                ],
            ],
        ];
    }

    /**
     * Daftar 10 Nilai Profil Pelajar Rahmatan Lil 'Alamin (P5RA Kemenag) beserta Sub-nilai.
     *
     * @return array<string, array{translation: string, sub_values: array<int, string>}>
     */
    public function getRahmatanLilAlaminValues(): array
    {
        return [
            'Ta’addub' => [
                'translation' => 'Berkeadaban',
                'sub_values' => [
                    'Menjunjung tinggi akhlak mulia dan kesopanan',
                    'Menghormati guru, orang tua, dan sesama',
                    'Menjaga integritas diri dalam pergaulan',
                ],
            ],
            'Qudwah' => [
                'translation' => 'Keteladanan',
                'sub_values' => [
                    'Menjadi pelopor kebaikan dan keteladanan',
                    'Konsisten antara perkataan dan perbuatan',
                    'Menginspirasi lingkungan sekitar untuk berbuat kebajikan',
                ],
            ],
            'Muwatanah' => [
                'translation' => 'Kewarganegaraan & Kebangsaan',
                'sub_values' => [
                    'Cinta tanah air dan komitmen kebangsaan',
                    'Menghargai simbol dan konstitusi negara',
                    'Menjaga persatuan dan keutuhan NKRI',
                ],
            ],
            'Tawassut' => [
                'translation' => 'Jalan Tengah / Moderat',
                'sub_values' => [
                    'Mengambil sikap moderat dalam beragama dan berbangsa',
                    'Menolak paham ekstremisme dan radikalisme',
                    'Menjaga keseimbangan pemikiran dan tindakan',
                ],
            ],
            'Tawazun' => [
                'translation' => 'Berimbang',
                'sub_values' => [
                    'Seimbang antara hak dan kewajiban',
                    'Keseimbangan antara kepentingan duniawi dan ukhrawi',
                    'Keseimbangan akal, rasa, dan jasmani',
                ],
            ],
            'I’tidal' => [
                'translation' => 'Lurus & Tegas',
                'sub_values' => [
                    'Menegakkan keadilan secara objektif',
                    'Bertindak lurus dan konsisten pada kebenaran',
                    'Tegas menolak kecurangan dan diskriminasi',
                ],
            ],
            'Musawah' => [
                'translation' => 'Kesetaraan',
                'sub_values' => [
                    'Menghargai martabat sesama manusia tanpa diskriminasi',
                    'Mendukung kesetaraan hak dalam masyarakat',
                    'Mengikis sifat feodalistik dan superioritas',
                ],
            ],
            'Syura' => [
                'translation' => 'Musyawarah',
                'sub_values' => [
                    'Mengutamakan dialog dan musyawarah mufakat',
                    'Menghargai pendapat orang lain secara terbuka',
                    'Menerima hasil keputusan bersama dengan ikhlas',
                ],
            ],
            'Tasamuh' => [
                'translation' => 'Toleransi',
                'sub_values' => [
                    'Menghargai perbedaan suku, agama, dan pandangan hidup',
                    'Membangun kerukunan antar umat beragama',
                    'Tidak memaksakan kehendak kepada orang lain',
                ],
            ],
            'Tatawwur wa Ibtikar' => [
                'translation' => 'Dinamis & Inovatif',
                'sub_values' => [
                    'Terbuka terhadap perubahan zaman dan teknologi yang positif',
                    'Kreatif dan inovatif menciptakan kemaslahatan',
                    'Semangat belajar sepanjang hayat (lifelong learning)',
                ],
            ],
        ];
    }
}
