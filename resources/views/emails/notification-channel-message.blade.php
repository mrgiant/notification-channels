@component('mail::message')

# @lang('Hello!')


{{ $text }}


@lang('Regards'),<br>
{{ config('app.name') }}

@endcomponent


