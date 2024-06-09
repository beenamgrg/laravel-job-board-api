<x-mail::message>
Greetings {{$data['employerName']}},


We have reviewed your application for your company, {{$data['companyName']}} registration. We are very happy to inform you that your application has been approved. Please use the email you provided, {{$data['employerEmail']}} for logging in.Welcome to {{ config('app.name') ?? 'laravel' }} family.


Thanks,<br>
{{ config('app.name') ?? 'laravel' }} 
</x-mail::message>