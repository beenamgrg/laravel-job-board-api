<x-mail::message>
Hello {{$data}},

Your Order No has been placed successfully and is now being processed

You can view your order details using the link below.


Thanks,<br>
{{ config('app.name') ?? 'laravel' }} 
</x-mail::message>