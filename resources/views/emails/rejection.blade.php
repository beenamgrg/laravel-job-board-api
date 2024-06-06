<x-mail::message>
Greetings {{$data['user']}},

Thank you for submitting your application and resume for our,{{$data['position']}} here at {{$data['company']}}. We deeply appreciate you taking the time to reach out to us. However, after reviewing your application, we have decided not to move forward with your application.

We want to thank you again for your interest in working with us and wish you the best of success in your future career endeavors.

Thanks,<br>
{{ Auth::user()->name }} 
{{$data['company']}}
</x-mail::message>