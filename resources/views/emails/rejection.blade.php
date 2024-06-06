<x-mail::message>
Greetings {{$data['applicant_name']}},

Thank you for submitting your application and resume for our, {{$data['job_title']}} here at, {{$data['company_name']}}. We deeply appreciate you taking the time to reach out to us. However, after reviewing your application, we have decided not to move forward with your application.

We want to thank you again for your interest in working with us and wish you the best of success in your future career endeavors.

Thanks,<br>
{{$data['company_name']}}
</x-mail::message>