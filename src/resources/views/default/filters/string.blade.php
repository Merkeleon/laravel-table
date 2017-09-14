<div class="form-group">
    <label>{{$label}}</label>
    <input @foreach ($attributes as $k => $v) {{ $k }}="{{ $v }}" @endforeach class="form-control" type="text"
    value="{{$value}}" name="f_{{$name}}"/>
</div>