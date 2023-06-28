<x-mail::message>
# FelixWorks - System Failure
 
There was a problem with the {{ $action }}.

Type: {{ $type }}.

Agency
<x-mail::panel>
{{ $agency }}
</x-mail::panel>
 
Data
<x-mail::panel>
{{ $data }}
</x-mail::panel>

Exception
<x-mail::panel>
{{ $exception }}
</x-mail::panel>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>