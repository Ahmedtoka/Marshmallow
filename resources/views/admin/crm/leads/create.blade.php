@extends('layouts.admin')
@section('title', 'Add lead')
@section('content')
    <x-admin.page-header title="Add lead" subtitle="For calls, WhatsApp messages, walk-ins and referrals." :back="route('admin.crm.leads.index')" />
    <form method="POST" action="{{ route('admin.crm.leads.store') }}" novalidate>
        @csrf
        @include('admin.crm.leads._form')
    </form>
@endsection
