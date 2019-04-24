<div class="form-group">
    <label>{{$label}}</label>
    <div class="row">
        <div class="col-xs-6 @if(array_get($error, 'from')) has-error @endif">
            <input @foreach (array_get($attributes, 'from', []) as $k => $v) {{ $k }}="{{ $v }}" @endforeach
            class="form-control" data-toggle="datepicker"  type="text" value="{{array_get($value, 'from')}}"
            placeholder="{{ trans('table::table.filter.date.from') }}" name="f_{{$name}}[from]"/>
            <span class="error">{{array_get($error, 'from')}}</span>
        </div>
        <div class="col-xs-6 @if(array_get($error, 'to')) has-error @endif">
            <input @foreach (array_get($attributes, 'to', []) as $k => $v) {{ $k }}="{{ $v }}" @endforeach
            class="form-control" data-toggle="datepicker"  type="text" value="{{array_get($value, 'to')}}"
            placeholder="{{ trans('table::table.filter.date.to') }}" name="f_{{$name}}[to]"/>
            <span class="error">{{array_get($error, 'to')}}</span>
        </div>
    </div>
</div>