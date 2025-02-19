<?php
namespace App\Domain\Customer\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ExportCompleted extends Notification implements ShouldQueue
{
    use Queueable;
    
    protected $filename;
    
    public function __construct($filename)
    {
        $this->filename = $filename;
    }
    
    public function via($notifiable)
    {
        return ['mail', 'database'];
    }
    
    public function toMail($notifiable)
    {
        $url = route('customer.exports.download', $this->filename);
        
        return (new MailMessage)
            ->subject('Your Customer Export is Ready')
            ->line('Your requested customer data export has been completed.')
            ->line('The data has been split into multiple Excel files and packaged as a ZIP archive.')
            ->action('Download ZIP Archive', $url)
            ->line('This download link will expire in 7 days.');
    }
    
    public function toArray($notifiable)
    {
        return [
            'message' => 'Your customer data export is ready for download',
            'filename' => $this->filename,
            'download_url' => route('customer.exports.download', $this->filename),
        ];
    }
}
