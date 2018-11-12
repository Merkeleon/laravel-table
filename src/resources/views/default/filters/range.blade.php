<div class="form__element form__element_filter_range @if(array_get($error, 'from') || array_get($error, 'to')) form__element_error @endif">
    <label>{{$label}}</label>
    <div class="form__element-container">
        <div class="input-group">
            <input class="form-control" type="text" value="{{array_get($value, 'from')}}" placeholder="{{ trans('table::table.filter.range.from') }}" name="f_{{$name}}[from]"/>
            <input class="form-control" type="text" value="{{array_get($value, 'to')}}" placeholder="{{ trans('table::table.filter.range.to') }}" name="f_{{$name}}[to]"/>
        </div>
        <div class="form__feedback">{{array_get($error, 'from')}}</div>
        <div class="form__feedback">{{array_get($error, 'to')}}</div>
    </div>
</div>