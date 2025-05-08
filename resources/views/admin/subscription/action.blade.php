{!! Form::open([
    'route' => [
        'follow.sub.instructor',
        [
            'influencer_id' => $follow->instructor->id,
        ],
    ],
    'method' => 'Post',
    'data-validate',
]) !!}
{{ Form::button(__('Cancel Subscription'), ['type' => 'submit', 'class' => 'btn btn-primary']) }}
{!! Form::close() !!}
