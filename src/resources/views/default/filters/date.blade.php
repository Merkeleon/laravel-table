<div class="form__element form__element_filter_date">
    <label>{{$label}}</label>
    <div class="form__element-container input-group">
        <input class="form-control" data-toggle="date-picker" type="text" value="{{array_get($value, 'from')}}" placeholder="{{ trans('table::table.filter.date.from') }}" name="f_{{$name}}[from]"/>
        <input class="form-control" data-toggle="date-picker" type="text" value="{{array_get($value, 'to')}}" placeholder="{{ trans('table::table.filter.date.to') }}" name="f_{{$name}}[to]"/>
    </div>
</div>