@extends('layouts.admin')
@section('title', 'Edit '.$lead->reference)
@section('content')
    <x-admin.page-header :title="'Edit '.$lead->parent_name" :subtitle="$lead->reference.' · status changes are made on the lead page'" :back="route('admin.crm.leads.show', $lead)" />
    <form method="POST" action="{{ route('admin.crm.leads.update', $lead) }}" novalidate>
        @csrf
        @method('PUT')
        @include('admin.crm.leads._form')
    </form>
@endsection
