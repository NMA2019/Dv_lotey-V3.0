<?php
// app/Jobs/SendPreinscriptionConfirmation.php

namespace App\Jobs;

use App\Mail\PreinscriptionConfirmation;
use App\Models\Preinscription;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SendPreinscriptionConfirmation implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $preinscription;
    public $tries = 3;
    public $timeout = 60;

    public function __construct(Preinscription $preinscription)
    {
        $this->preinscription = $preinscription;
    }

    public function handle()
    {
        Mail::to($this->preinscription->email)
            ->send(new PreinscriptionConfirmation($this->preinscription));
    }

    public function failed(\Exception $exception)
    {
        \Log::error('Échec envoi email confirmation: ' . $exception->getMessage(), [
            'preinscription_id' => $this->preinscription->id,
            'email' => $this->preinscription->email
        ]);
    }
}