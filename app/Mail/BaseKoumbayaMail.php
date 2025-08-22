<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

abstract class BaseKoumbayaMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->from(config('mail.from.address'), 'Koumbaya')
                    ->with([
                        'unsubscribeUrl' => $this->getUnsubscribeUrl(),
                    ]);
    }

    /**
     * Get unsubscribe URL
     */
    protected function getUnsubscribeUrl()
    {
        return config('app.url') . '/unsubscribe';
    }

    /**
     * Set high priority for important emails
     */
    protected function setHighPriority()
    {
        $this->withSwiftMessage(function ($message) {
            $message->setPriority(1);
        });
        
        return $this;
    }

    /**
     * Add tracking headers
     */
    protected function addTracking($category = 'general')
    {
        $this->withSwiftMessage(function ($message) use ($category) {
            $headers = $message->getHeaders();
            $headers->addTextHeader('X-Koumbaya-Category', $category);
            $headers->addTextHeader('X-Koumbaya-Version', '1.0');
        });

        return $this;
    }
}