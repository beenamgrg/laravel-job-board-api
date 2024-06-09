<x-mail::message>
Greetings {{$data['employerName']}},


We have reviewed your application for your company, {{$data['companyName']}} registration. We are very sorry to inform you that your application has been rejected.


Thanks,<br>
{{ config('app.name') ?? 'laravel' }} 
</x-mail::message>