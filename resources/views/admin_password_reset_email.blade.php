<x-mail::message>
# Introduction

Hi , {{$name}}Regarding to your request , we sent resent code belwo

<x-mail::panel >
    Reset code is :{{ $ResentCode }}
</x-mail::panel>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
