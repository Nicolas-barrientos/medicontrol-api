<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Medicamento;
use Carbon\Carbon;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Kreait\Laravel\Firebase\Facades\Firebase;

class EnviarRecordatorios extends Command
{
    protected $signature = 'medicamentos:recordatorios';
    protected $description = 'Enviar notificaciones de recordatorio de medicamentos';

    public function handle()
    {
        $horaActual = Carbon::now()->format('H:i');

        $medicamentos = Medicamento::where('hora_recordatorio', $horaActual)->get();

        foreach ($medicamentos as $med) {
            if (!$med->user->fcm_token) continue; // Cada usuario debe tener un token FCM

            $message = CloudMessage::withTarget('token', $med->user->fcm_token)
                ->withNotification(Notification::create(
                    'Recordatorio de Medicamento',
                    "Es hora de tomar: {$med->nombre}"
                ));

            Firebase::messaging()->send($message);
        }

        $this->info('Notificaciones enviadas correctamente');
    }
}
