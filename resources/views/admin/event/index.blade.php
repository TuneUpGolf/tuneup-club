@extends('layouts.main')
@section('title', __('Event'))
@section('breadcrumb')
    <li class="breadcrumb-item">{!! Html::link(route('home'), __('Dashboard'), ['']) !!}</li>
    <li class="breadcrumb-item">{{ __('Event') }}</li>
@endsection
@push('css')
    <link rel="stylesheet" href="{{ asset('assets/css/plugins/main.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/plugins/datepicker-bs5.min.css') }}">
@endpush
@section('action-btn')
    <div class="float-end">
        @can('create-event')
            <a href="javascript:void(0);" data-size="lg" data-bs-placement="bottom" id="EventCalender"
                data-url="{{ route('event.create') }}" data-ajax-popup="true" data-kt-modal="true" data-bs-toggle="tooltip"
                title="{{ __('Create') }}" data-title="{{ __('Create New Event') }}" class="btn btn-sm btn-primary">
                <i class="ti ti-plus"></i>
            </a>
        @endcan
    </div>
@endsection
@section('content')
    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <div class="row align-items-center">
                        <div class="col-lg-6">
                            <h5>{{ __('Calendar') }}</h5>
                        </div>
                        <div class="col-lg-6">
                            @if (Utility::getsettings('google_calendar_enable') && Utility::getsettings('google_calendar_enable') == 'on')
                                <select class="form-control float-end w-50" name="calenderType" id="calenderType"
                                    onchange="get_data()">
                                    <option value="google_calender">{{ __('Google Calender') }}</option>
                                    <option value="local_calender" selected="true">{{ __('Local Calender') }}</option>
                                </select>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div id='calendar' class='calendar'></div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card">
                <div class="card-body">
                    <h6 class="mb-4">{{ __('Upcoming Events') }}</h6>
                    <ul class="mt-3 event-cards list-group list-group-flush w-100">
                        <li class="mb-3 list-group-item card">
                            <div class="row align-items-center justify-content-between">
                                <div class="align-items-center">
                                    @if (!$events->isEmpty())
                                        @forelse ($currentMonthEvents as $currentMonthEvent)
                                            <div class="mb-3 border shadow-none card">
                                                <div class="px-3">
                                                    <div class="row align-items-center">
                                                        <div class="col ml-n2">
                                                            <h5 class="mb-3 text-sm fc-event-title-container">
                                                                <a href="javascript:void(0);" data-size="lg"
                                                                    data-url="{{ route('event.edit', $currentMonthEvent->id) }}"
                                                                    data-ajax-popup="true" id="editEvent"
                                                                    data-title="{{ __('Edit Event') }}"
                                                                    class="fc-event-title text-primary">
                                                                    {{ $currentMonthEvent->title }}
                                                                </a>
                                                            </h5>
                                                            <p class="card-text small text-dark">
                                                                {{ __('Start Date : ') }}
                                                                {{ Utility::date_format($currentMonthEvent->start_date) }}<br>
                                                                {{ __('End Date : ') }}
                                                                {{ Utility::date_format($currentMonthEvent->end_date) }}
                                                            </p>
                                                        </div>
                                                        <div class="col-auto text-right d-flex">
                                                            @can('edit-event')
                                                                <div class="action-btn bg-primary ms-2">
                                                                    <a class="rounded btn btn-sm small btn-warning edit_form cust_btn"
                                                                        data-url="{{ route('event.edit', $currentMonthEvent->id) }}"
                                                                        href="javascript:void(0);" data-bs-toggle="tooltip"
                                                                        data-bs-placement="bottom"
                                                                        data-bs-original-title="{{ __('Edit') }}"
                                                                        id="editEvent">
                                                                        <i class="ti ti-edit"></i>
                                                                    </a>
                                                                </div>
                                                            @endcan
                                                            @can('delete-event')
                                                                <div class="action-btn bg-danger ms-2">
                                                                    {!! Form::open([
                                                                        'method' => 'DELETE',
                                                                        'route' => ['event.destroy', $currentMonthEvent->id],
                                                                        'id' => 'delete-form-' . $currentMonthEvent->id,
                                                                        'class' => 'd-inline',
                                                                    ]) !!}
                                                                    <a href="javascript:void(0);"
                                                                        class="rounded btn btn-sm small btn-danger show_confirm"
                                                                        data-bs-toggle="tooltip" data-bs-placement="bottom"
                                                                        data-bs-original-title="{{ __('Delete') }}"
                                                                        id="delete-form-{{ $currentMonthEvent->id }}">
                                                                        <i class="mr-0 ti ti-trash"></i>
                                                                        </a>
                                                                    {!! Form::close() !!}
                                                                </div>
                                                            @endcan
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @empty
                                            <tr>
                                                <td colspan="4">
                                                    <div class="text-center">
                                                        <h6>{{ __('There is no event in this month') }}</h6>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforelse
                                    @else
                                        <div class="text-center">
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('javascript')
    <script src="{{ asset('assets/js/plugins/main.min.js') }}"></script>
    <script src="{{ asset('vendor/modules/moment.min.js') }}"></script>
    <script src="{{ asset('assets/js/plugins/datepicker-full.min.js') }}"></script>
    <script src="{{ asset('assets/js/plugins/choices.min.js') }}"></script>
    <script type="text/javascript">
        $(document).ready(function() {
            get_data();
        });

        function get_data() {
            var calenderType = $('#calenderType :selected').val();
            $('#calendar').removeClass('local_calender');
            $('#calendar').removeClass('google_calender');
            if (calenderType == undefined) {
                calenderType = 'local_calender';
            }
            $('#calendar').addClass(calenderType);
            $.ajax({
                url: '{{ route('event.get.data') }}',
                method: "POST",
                data: {
                    "_token": "{{ csrf_token() }}",
                    'calenderType': calenderType
                },
                success: function(data) {
                    (function() {
                        var etitle;
                        var etype;
                        var etypeclass;
                        var calendar = new FullCalendar.Calendar(document.getElementById('calendar'), {
                            headerToolbar: {
                                left: 'prev,next today',
                                center: 'title',
                                right: 'dayGridMonth,timeGridWeek,timeGridDay'
                            },
                            select: function(date) {
                                var url = $('#EventCalender').data('url');
                                var start = date.startStr;
                                var end = date.endStr;
                                $.ajax({
                                    type: 'GET',
                                    url: url,
                                    data: {
                                        start_date: start,
                                        end_date: end
                                    },
                                    success: function(response) {
                                        $("#common_modal .modal-title").html(
                                            '{{ __('Create Event') }}');
                                        $("#common_modal .body").html(response);
                                        var multipleCancelButton = new Choices(
                                            '#user', {
                                                removeItemButton: true,
                                            });
                                        const start_date = new Datepicker(document
                                            .querySelector(
                                                '#start_date'), {
                                                buttonClass: 'btn',
                                                format: 'dd/mm/yyyy'
                                            });
                                        const end_date = new Datepicker(document
                                            .querySelector(
                                                '#end_date'), {
                                                buttonClass: 'btn',
                                                format: 'dd/mm/yyyy'
                                            });
                                        $("#common_modal").modal('show');
                                    },
                                    error: function(response) {}
                                });
                            },
                            buttonText: {
                                timeGridDay: "{{ __('Day') }}",
                                timeGridWeek: "{{ __('Week') }}",
                                dayGridMonth: "{{ __('Month') }}"
                            },
                            themeSystem: 'bootstrap',
                            slotDuration: '00:10:00',
                            navLinks: true,
                            droppable: true,
                            selectable: true,
                            selectMirror: true,
                            editable: true,
                            dayMaxEvents: true,
                            handleWindowResize: true,
                            events: data,
                        });
                        calendar.render();
                    })();
                }
            });
        }

        $(document).on('click', '#EventCalender', function() {
            var url = $(this).attr('data-url');
            $.ajax({
                type: 'GET',
                url: url,
                data: {},
                success: function(response) {
                    $("#common_modal .modal-title").html('{{ __('Create Event') }}');
                    $("#common_modal .body").html(response);
                    var multipleCancelButton = new Choices('#user', {
                        removeItemButton: true,
                    });

                    const start_date = new Datepicker(document
                        .querySelector(
                            '#start_date'), {
                            buttonClass: 'btn',
                            format: 'dd/mm/yyyy'
                        });
                    const end_date = new Datepicker(document
                        .querySelector(
                            '#end_date'), {
                            buttonClass: 'btn',
                            format: 'dd/mm/yyyy'
                        });
                    $("#common_modal").modal('show');
                },
                error: function(response) {}
            });
        });

        $(document).on('click', '#editEvent,.fc-event', function(e) {
            e.preventDefault();
            if ($(this).attr('data-url')) {
                var url = $(this).attr('data-url');
            } else {
                var url = $(this).attr('href');
            }
            $.ajax({
                type: 'GET',
                url: url,
                data: {},
                success: function(response) {
                    $("#common_modal .modal-title").html('{{ __('Edit Event') }}');
                    $("#common_modal .body").html(response);
                    var startDate = $("#common_modal .body").find('input[name="start_date"]').val();
                    var endDate = $("#common_modal .body").find('input[name="end_date"]').val();
                    var multipleCancelButton = new Choices('#user', {
                        removeItemButton: true,
                    });
                    const start_date = new Datepicker(document
                        .querySelector(
                            '#start_date'), {
                            buttonClass: 'btn',
                            format: 'dd/mm/yyyy'
                        });
                    const end_date = new Datepicker(document
                        .querySelector(
                            '#end_date'), {
                            buttonClass: 'btn',
                            format: 'dd/mm/yyyy'
                        });
                    $("#common_modal").modal('show');
                },
                error: function(response) {}
            });
        });
    </script>
@endpush
