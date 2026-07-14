<?php

namespace Platform\Academy\Livewire\Certificate;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Platform\Academy\Models\AcademyCertificate;

class Show extends Component
{
    public string $uuid;

    public function mount(string $uuid): void
    {
        $this->uuid = $uuid;
    }

    public function render()
    {
        $user = Auth::user();

        $certificate = AcademyCertificate::query()
            ->where('uuid', $this->uuid)
            ->where('team_id', $user->currentTeam->id)
            ->with(['path.category', 'user'])
            ->firstOrFail();

        $this->dispatch('comms', [
            'model' => AcademyCertificate::class,
            'modelId' => $certificate->id,
            'subject' => 'Zertifikat: ' . ($certificate->path?->title ?? ''),
            'description' => 'Abschlusszertifikat ' . $certificate->serial,
            'url' => route('academy.certificates.show', ['uuid' => $certificate->uuid]),
            'source' => 'academy.certificates.show',
            'recipients' => [],
            'meta' => ['view_type' => 'show', 'resource' => 'certificate'],
        ]);

        return view('academy::livewire.certificate.show', [
            'certificate' => $certificate,
            'path' => $certificate->path,
            'holder' => $certificate->user,
            'accentColor' => $certificate->path?->coverColor() ?? '#4F46E5',
        ])->layout('platform::layouts.app');
    }
}
