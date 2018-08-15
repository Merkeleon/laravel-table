<div class="form__element form__element_filter_range">
    <label>{{$label}}</label>
    <div class="form__element-container input-group">
        <input class="form-control" type="text" value="{{array_get($value, 'from')}}" placeholder="{{ trans('table::table.filter.range.from') }}" name="f_{{$name}}[from]"/>
        <input class="form-control" type="text" value="{{array_get($value, 'to')}}" placeholder="{{ trans('table::table.filter.range.to') }}" name="f_{{$name}}[to]"/>
    </div>
</div>