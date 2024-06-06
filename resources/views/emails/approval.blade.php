<x-mail::message>
Greetings {{$data['user']}},

We have reviewed your job application for the position ,{{$data['position']}} here at {{$data['company']}}. We are very happy to inform you that your application has been approved. Please visit the office for the further interview tomorrow at 1 p.m.

Feel free to contact us  {{$data['company_email']}} if you have any queries.


Thanks,<br>
{{ Auth::user()->name }} 
{{$data['company']}}
</x-mail::message>