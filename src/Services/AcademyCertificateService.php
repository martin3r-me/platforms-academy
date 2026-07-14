<?php

namespace Platform\Academy\Services;

use Illuminate\Support\Str;
use Platform\Academy\Models\AcademyCertificate;
use Platform\Academy\Models\AcademyPath;

class AcademyCertificateService
{
    /**
     * Stellt ein Zertifikat aus, sobald ein Kurs zu 100% abgeschlossen ist.
     * Idempotent: existiert bereits eines fuer User+Kurs, wird es zurueckgegeben.
     */
    public function issueIfComplete(int $userId, AcademyPath $path): ?AcademyCertificate
    {
        $existing = $path->certificateFor($userId);
        if ($existing) {
            return $existing;
        }

        $progress = $path->progressFor($userId);
        if ($progress['total'] === 0 || $progress['completed'] < $progress['total']) {
            return null;
        }

        return AcademyCertificate::create([
            'user_id' => $userId,
            'academy_path_id' => $path->id,
            'team_id' => $path->team_id,
            'serial' => $this->generateSerial($path),
            'issued_at' => now(),
        ]);
    }

    public function forUserPath(int $userId, AcademyPath $path): ?AcademyCertificate
    {
        return $path->certificateFor($userId);
    }

    /**
     * Seriennummer im Format CODE-JAHR-LFDNR, z. B. VO-2026-0007.
     * Faellt der Kurs ohne Code, wird eine Kurzform aus der Path-ID genutzt.
     */
    protected function generateSerial(AcademyPath $path): string
    {
        $code = $path->code
            ? Str::upper(preg_replace('/[^A-Za-z0-9]/', '', $path->code))
            : ('P' . $path->id);
        $year = now()->format('Y');

        $seq = AcademyCertificate::where('academy_path_id', $path->id)->count() + 1;

        do {
            $serial = sprintf('%s-%s-%04d', $code, $year, $seq);
            $seq++;
        } while (AcademyCertificate::where('serial', $serial)->exists());

        return $serial;
    }
}
