<x-mail::message>
Greetings {{$data['employerName']}},

A new applicant, {{$data['applicantName']}}, with {{$data['applicantEmail']}} has applied to the position, ({{$data['jobTitle']}})that you have posted for your company, {{$data['companyName']}}

You can view the details below : <br>
Coverletter : <br>
{{$data['applicantCoverLetter']}}
<br>
<a href="{{url($data['applicantResume'])}}">Resume</a>


Thanks,<br>
{{ config('app.name') ?? 'laravel' }} 
</x-mail::message>