<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DrawHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'lottery_id',
        'method',
        'seed',
        'total_tickets',
        'winning_position',
        'winning_ticket_number',
        'winner_user_id',
        'drawn_by',
        'draw_data',
        'notes',
        'drawn_at',
    ];

    protected function casts(): array
    {
        return [
            'draw_data' => 'array',
            'drawn_at' => 'datetime',
        ];
    }

    /**
     * Relations
     */
    public function lottery()
    {
        return $this->belongsTo(Lottery::class, 'lottery_id');
    }

    public function winningTicket()
    {
        return $this->belongsTo(LotteryTicket::class, 'winning_ticket_id');
    }

    public function winner()
    {
        return $this->belongsTo(User::class, 'winner_user_id');
    }

    /**
     * Verify the draw integrity
     */
    public function verifyIntegrity(): bool
    {
        $expectedHash = hash('sha256', 
            $this->lottery_id . 
            $this->winning_ticket_id . 
            $this->draw_seed
        );
        
        return $this->verification_hash === $expectedHash;
    }

    /**
     * Get human readable draw method
     */
    public function getDrawMethodLabelAttribute(): string
    {
        return match($this->draw_method) {
            'automatic' => 'Tirage automatique',
            'manual' => 'Tirage manuel',
            'scheduled' => 'Tirage programmé',
            default => 'Méthode inconnue'
        };
    }

    /**
     * Get initiator information
     */
    public function getInitiatorAttribute()
    {
        if ($this->initiated_by === 'system') {
            return 'Système automatique';
        }
        
        if (str_starts_with($this->initiated_by, 'user_')) {
            $userId = str_replace('user_', '', $this->initiated_by);
            $user = User::find($userId);
            return $user ? $user->first_name . ' ' . $user->last_name : 'Utilisateur inconnu';
        }
        
        return $this->initiated_by;
    }
}