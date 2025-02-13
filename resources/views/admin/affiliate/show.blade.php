@extends('adminlte::page')

@section('title', 'View Affiliate Talent')

@section('content_header')
    <div class="d-flex justify-content-between">
        <h1>Affiliate Talent Details</h1>
        <a href="{{ route('affiliate.edit', $affiliate->id) }}" class="btn btn-warning">Edit</a>
    </div>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <table class="table">
                        <tr>
                            <th>Username</th>
                            <td>{{ $affiliate->username }}</td>
                        </tr>
                        <tr>
                            <th>PIC</th>
                            <td>{{ $affiliate->pic }}</td>
                        </tr>
                        <tr>
                            <th>GMV Range</th>
                            <td>{{ $affiliate->gmv_bottom }} - {{ $affiliate->gmv_top }}</td>
                        </tr>
                        <tr>
                            <th>Rate Card</th>
                            <td>{{ $affiliate->rate_card }}</td>
                        </tr>
                        <tr>
                            <th>Final Rate Card</th>
                            <td>{{ $affiliate->rate_card_final }}</td>
                        </tr>
                        <tr>
                            <th>ROAS</th>
                            <td>{{ $affiliate->roas }}</td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table">
                        <tr>
                            <th>Instagram</th>
                            <td>{{ $affiliate->contact_ig }}</td>
                        </tr>
                        <tr>
                            <th>WhatsApp</th>
                            <td>{{ $affiliate->contact_wa_notelp }}</td>
                        </tr>
                        <tr>
                            <th>TikTok</th>
                            <td>{{ $affiliate->contact_tiktok }}</td>
                        </tr>
                        <tr>
                            <th>Email</th>
                            <td>{{ $affiliate->contact_email }}</td>
                        </tr>
                        <tr>
                            <th>Platform</th>
                            <td>{{ $affiliate->platform_menghubungi }}</td>
                        </tr>
                        <tr>
                            <th>Status</th>
                            <td>{{ $affiliate->status_call }}</td>
                        </tr>
                    </table>
                </div>
            </div>

            <div class="mt-4">
                <h4>Additional Information</h4>
                <p>{{ $affiliate->keterangan }}</p>
            </div>

            <div class="mt-4">
                <a href="{{ route('affiliate.index') }}" class="btn btn-secondary">Back to List</a>
            </div>
        </div>
    </div>
@stop