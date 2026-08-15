@extends('app')

@section('content')
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">{{ __('Customers') }}</h1>
    <a href="{{ route('add_customer') }}" class="btn btn-sm btn-primary shadow-sm mt-3 mt-sm-0">
        <i class="fas fa-plus fa-sm text-white-50"></i> {{ __('Add Customer') }}
    </a>
</div>

<div class="card shadow mb-4">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead><tr><th>{{ __('Customer') }}</th><th>{{ __('Plan') }}</th><th>{{ __('Status') }}</th><th class="text-right">{{ __('Actions') }}</th></tr></thead>
                <tbody>
                    @forelse($list as $customer)
                        <tr>
                            <td class="align-middle"><strong>{{ $customer->username }}</strong></td>
                            <td class="align-middle">{{ $customer->plan?->name ?: '—' }}</td>
                            <td class="align-middle"><span class="badge badge-{{ $customer->active ? 'success' : 'secondary' }}">{{ $customer->active ? __('Active') : __('Inactive') }}</span></td>
                            <td class="align-middle text-right text-nowrap">
                                <a href="{{ route('show_customer', $customer->id) }}" class="btn btn-sm btn-outline-primary">{{ __('edit') }}</a>
                                <form action="{{ route('delete_customer', $customer->id) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('Delete this customer?') }}')">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-danger">{{ __('delete') }}</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center text-muted py-4">{{ __('No customers registered yet.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($list->hasPages())<div class="mt-4">{{ $list->links('pagination::bootstrap-4') }}</div>@endif
    </div>
</div>
@endsection
