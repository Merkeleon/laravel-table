<div class="form__element form__element_filter_text @if($error) form__element_error @endif">
    <label>{{$label}}</label>
    <div class="form__element-container">
        <input class="form-control" type="text" value="{{$value}}" name="f_{{$name}}"/>
        <div class="form__feedback">{{$error}}</div>
    </div>
</div>