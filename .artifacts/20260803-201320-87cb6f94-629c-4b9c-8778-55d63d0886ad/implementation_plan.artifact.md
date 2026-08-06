# Fix Missing `pengajuanMagang` Relationship in `Mahasiswa` Model

The error `Call to undefined method App\Models\Mahasiswa::pengajuanMagang()` occurs because `WeeklyLogbookResource.php` attempts to filter records using a relationship that doesn't exist in the `Mahasiswa` model (which currently uses `pengajuan()`).

## Proposed Changes

### Models

#### [Mahasiswa.php](file:///C:/SKRIPSI/apk/app/Models/Mahasiswa.php)

- Add a `pengajuanMagang()` relationship method that points to the `PengajuanMagang` model.
- I will keep the existing `pengajuan()` method to avoid breaking other parts of the system, or simply alias it.

```php
    /**
     * Relasi ke pengajuan magang (alias untuk pengajuan)
     */
    public function pengajuanMagang()
    {
        return $this->hasMany(PengajuanMagang::class);
    }
```

## Verification Plan

### Manual Verification
- Log in as a **Pembimbing**.
- Navigate to the **Weekly Logbook** menu.
- Verify that the list of logbooks loads correctly without the `BadMethodCallException`.
- Verify that only logbooks from assigned students are visible.
