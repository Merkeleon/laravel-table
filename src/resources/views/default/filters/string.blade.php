<div class="form-group @if($error) has-error @endif">
    <label>{{$label}}</label>
    <input @foreach ($attributes as $k => $v) {{ $k }}="{{ $v }}" @endforeach class="form-control" type="text"
    value="{{$value}}" name="f_{{$name}}"/>
    <span class="error">{{$error}}</span>
</div>