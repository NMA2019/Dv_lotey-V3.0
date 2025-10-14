<?php
// app/Mail/PreinscriptionConfirmation.php

namespace App\Mail;

use App\Models\Preinscription;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PreinscriptionConfirmation extends Mailable
{
    use Queueable, SerializesModels;

    public $preinscription;

    public function __construct(Preinscription $preinscription)
    {
        $this->preinscription = $preinscription;
    }

    public function build()
    {
        return $this->subject('✅ Confirmation de votre préinscription - ' . $this->preinscription->numero_dossier)
                    ->view('emails.preinscription-confirmation')
                    ->with([
                        'preinscription' => $this->preinscription,
                    ]);
    }
}