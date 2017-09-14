<div class="form-group">
    <label>{{$label}}</label>
    <div class="row">
        <div class="col-xs-6">
            <input @foreach (array_get($attributes, 'from', []) as $k => $v) {{ $k }}="{{ $v }}" @endforeach
            class="form-control" data-toggle="datepicker"  type="text" value="{{array_get($value, 'from')}}"
            placeholder="{{ trans('table::filter.date.from') }}" name="f_{{$name}}[from]"/>
        </div>
        <div class="col-xs-6">
            <input @foreach (array_get($attributes, 'to', []) as $k => $v) {{ $k }}="{{ $v }}" @endforeach
            class="form-control" data-toggle="datepicker"  type="text" value="{{array_get($value, 'to')}}"
            placeholder="{{ trans('table::filter.date.to') }}" name="f_{{$name}}[to]"/>
        </div>
    </div>
</div>