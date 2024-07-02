<table id="bofuTable" class="table table-bordered table-striped dataTable responsive" aria-describedby="bofu-info" width="100%">
    <thead>
    <tr>
        <th>{{ trans('labels.date') }}</th>
        <th>{{ trans('labels.spend') }}</th>
        <th>{{ trans('labels.atc') }}</th>
        <th data-toggle="tooltip" data-placement="top" title="{{ trans('labels.initiated_checkout_number') }}">{{ trans('labels.ic_short') }}</th>
        <th>{{ trans('labels.purchase_number') }}</th>
        <th data-toggle="tooltip" data-placement="top" title="{{ trans('labels.cost_per_ic') }}">{{ trans('labels.cost_per_ic_short') }}</th>
        <th>{{ trans('labels.cost_per_atc') }}</th>
        <th>{{ trans('labels.cost_per_purchase') }}</th>
        <th>{{ trans('labels.roas') }}</th>
        <th>{{ trans('labels.frequency') }}</th>
        <th>{{ trans('labels.action') }}</th>
    </tr>
    </thead>
</table>
