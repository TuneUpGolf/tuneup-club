{!! Form::open([
    'route' => [
        'follow.sub.influencer',
        [
            'influencer_id' => $follow->influencer->id,
        ],
    ],
    'method' => 'Post',
    'data-validate',
]) !!}
{{ Form::button(__('Cancel Subscription'), ['type' => 'submit', 'class' => 'btn btn-primary']) }}
{!! Form::close() !!}
