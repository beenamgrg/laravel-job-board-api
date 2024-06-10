<x-mail::message>
Greetings {{$data['applicant_name']}},


We have reviewed your job application for the position, {{$data['job_title']}} here at, {{$data['company_name']}}. We are very happy to inform you that your application has been approved. Please visit the office for the further interview tomorrow at 1 p.m.

Feel free to contact us at {{$data['company_email']}} if you have any queries.


Thanks,<br>
{{$data['company_name']}}<br>
{{ config('app.name') ?? 'laravel' }} 
</x-mail::message>