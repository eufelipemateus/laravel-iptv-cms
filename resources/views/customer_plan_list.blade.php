@extends('app')

@section('content')
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">{{ __('Plans') }}</h1>
    <a href="{{ route('add_customer_plan') }}" class="btn btn-sm btn-primary shadow-sm mt-3 mt-sm-0">
        <i class="fas fa-plus fa-sm text-white-50"></i> {{ __('Add Plan') }}
    </a>
</div>

<div class="card shadow mb-4">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead><tr><th>{{ __('Name') }}</th><th>{{ __('Price') }}</th><th>{{ __('Status') }}</th><th>{{ __('Type') }}</th><th class="text-right">{{ __('Actions') }}</th></tr></thead>
                <tbody>
                    @forelse($list as $plan)
                        <tr>
                            <td class="align-middle"><strong>{{ $plan->name }}</strong></td>
                            <td class="align-middle">{{ $plan->price }}</td>
                            <td class="align-middle"><span class="badge badge-{{ $plan->active ? 'success' : 'secondary' }}">{{ $plan->active ? __('Active') : __('Inactive') }}</span></td>
                            <td class="align-middle">{{ $plan->additional ? __('Additional') : __('Main') }}</td>
                            <td class="align-middle text-right text-nowrap">
                                <a href="{{ route('show_customer_plan', $plan->id) }}" class="btn btn-sm btn-outline-primary">{{ __('edit') }}</a>
                                <form action="{{ route('delete_customer_plan', $plan->id) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('Delete this plan?') }}')">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-danger">{{ __('delete') }}</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted py-4">{{ __('No plans registered yet.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($list->hasPages())<div class="mt-4">{{ $list->links('pagination::bootstrap-4') }}</div>@endif
    </div>
</div>
@endsection
