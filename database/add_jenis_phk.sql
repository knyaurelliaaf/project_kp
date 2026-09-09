-- Tambahkan sekali bila jenis surat PHK belum tersedia di database.
INSERT INTO jenis_surat (kode, nama_surat, format_nomor)
SELECT 'PHK', 'Surat Pemutusan Hubungan Kerja', 'PHK/{rig}/ADK/SUB GDAP/{tahun}/{bulan}/{no}'
WHERE NOT EXISTS (
    SELECT 1 FROM jenis_surat WHERE kode = 'PHK'
);
