<x-mail::message>
Greetings {{ config('app.name') ?? env('APP_NAME') }} ,


A new company named,{{$data['company_name']}} has registered for your service. The details of the company is given below :<br>
{{$data}}


Thanks,<br>
{{$data['company_name']}}
{{ config('app.name') ?? env('APP_NAME') }} 
</x-mail::message>