@extends ('headers.header')

@section('content')
<link rel="stylesheet" type="text/css" href="{{ URL::asset('css/QnA.css') }}">

<div>
	@auth
		<input class="useradmininput" type="hidden" value="{{ Auth::user()->admin }}">
	@else
		<input class="useradmininput" type="hidden" value="0">
	@endauth
</div>


<!-- 
<div id="qna_div">
	<table class="table-container">
		<thead>
			<tr>
				<th><h1>번호</h1></th>
				<th><h1>제목</h1></th>
				<th><h1>작성자</h1></th>
				<th><h1>작성일</h1></th>
			</tr>
		</thead>
		@foreach ($questions as $question)
			<tbody id="{{ $question->id }}">
				<tr class="title">
						<td>
							<p>{{ $question->id }}</p>
						</td>
						<td>
							<p>{{ $question->title }}</p>
						</td>
						<td>
							<p>{{ $question->user->name }}</p>
						</td>
						<td>
							<p>{{ $question->created_at }}</p>
						</td>
				</tr>

				<tr name="content">
					<td colspan="4">
						<p>{{ $question->content }}</p>
						@auth
							@if ( Auth::user()->admin == 1 )
								<button class="btn-delete">
									삭제
								</button>
							@endif
						@endauth
					</td>
				</tr>
			</tbody>
		@endforeach
	</table>
</div>
 -->

<div class="table-responsive">
  <table class="table table-bordered table-striped" id="question_table">
    <thead>
      <tr>
        <th width="10%">번호</th>
        <th width="35%">제목</th>
        <th width="35%">작성자</th>
        <th width="50%">작성일</th>
      </tr>
    </thead>
  </table>
</div>
<br>
<br>

<div class="question-list">
  <h1 style="color: #FFFFFF;">질문 목록</h1>
  <hr/>
  <div id="question-list">
    <ul>
        @forelse($questions as $question)
          <li class="openQuestion" id="ques_{{$question->id}}">
            <p id="questionId" style="color: #FFFFFF;">{{ $question->id }}</p>
            <p> {{ $question->title }} </p>
            <small style="color: #FFFFFF;"> by {{ $question->user->name }} </small>
          </li>
          <div id="option_{{$question->id}}"></div>
        @empty
          <p style="color: #FFFFFF;">글이 없습니다</p>
        @endforelse
    </ul>
  </div>
</div>

<!-- Trigger Modal -->
<div class="Align_Center">
    <button type="button" id="createQuestion" name="createQuestion" class="btn btn-success btn-sm" data-toggle="modal" data-target="#questionModal" data-backdrop="false">Create Question</button>
</div>

<!-- 질문글 작성 모달창 -->
<div class="modal fade" id="questionModal" role="dialog">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalTitle">새 글 쓰기</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
      </div>

      <div class="modal-body">
        <span id="form_result"></span>
        <form method="post" id="question-form">
          @csrf

          <div class="form-group {{ $errors->has('title') ? 'has-error' : '' }}">
            <label for="title" class="col-form-label">제목</label>
            <input type="text" class="form-control" id="title" name="title" value="{{ old('title') }}">
            <!-- {!! $errors->first('title', '<span class="form-error">:message</span>') !!} -->
          </div>

          <div class="form-group {{ $errors->has('content') ? 'has-error' : '' }}">
            <label for="content" class="col-form-label">본문</label>
            <textarea class="form-control" name="content" id="content">{{ old('content') }}</textarea>
            <!-- {!! $errors->first('content', '<span class="form-error">:message</span>') !!} -->
          </div>
          
          <div class="form-group">
            <input type="hidden" name="hidden_id" id="hidden_id" value="{{ Auth::id() }}">
            <input type="submit" name="action_button" id="action_button" class="btn btn-warning" value="Add">

          </div>

        </form>
      </div>

      <div class="modal-footer">
        
        <button id="closeQuestionModal" type="button" class="btn btn-secondary" data-dismiss="modal">닫기</button>
      </div>
    </div>
  </div>
</div>

@stop

@section('script')
<script src="//code.jquery.com/jquery-1.11.3.min.js"></script>
<script src="{{ URL::asset('js\jquery-3.2.1.min.js') }}"></script>
<script src="{{ URL::asset('css\styles\bootstrap-4.1.2\bootstrap.min.js') }}"></script>
<link rel="stylesheet" type="text/css" href="{{ URL::asset('css\styles\bootstrap-4.1.2\bootstrap.min.css') }}">
<script>
$(document).ready(function(){
  $.ajaxSetup({
    headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
  });

  // Modal 띄우기
  $('#questionModalBtn').click(function(e){
    e.preventDefault();
    console.log('event emitted');
    // $('#questionModal').modal();
    $('#questionModal').modal('show');
  });

  // Create a Question ajax
  $('#createQuestion').click(function(e){
    $('#questionModal').modal('show');
  });

  $('#question-form').on('submit', function(e){
    e.preventDefault();
    if( $('#action_button').val() == 'Add' ){
      $('#questionModal').modal('hide');
      $.ajax({
        url: "{{ route('qna.store') }}",
        method: "POST",
        data: new FormData(this),
        contentType: false,
        cache: false,
        processData: false,
        dataType: 'json',
        success: function(data){
          console.log('success');
          console.log(data); // {success: "Data Added Successfully!"}
          var html = '';
          if(data.errors){
            html = '<div class="alert alert-danger">';
            for(var count = 0; count < data.errors.length; count++){
              html += '<p>' + data.errors[count] + '</p>';
            }
            html += '</div>';
          }
          
          if(data.success){
            html = '<div class="alert alert-success">' + data.success + '</div>';
            $('#question-form')[0].reset();
            $('#question-list').DataTable().ajax.reload();
          }

          $('#form_result').html(html);
        }
      });
    }
  });

  var selected = -1;

  document.querySelectorAll('.openQuestion').forEach(function (e){
    e.addEventListener('click',function(){
      let id = e.querySelector('#questionId').innerHTML;
      onClick(id);
    });
  });

  function onClick(id){
    console.log('글 클릭함');
    $.ajax({
      type: 'get',
      url: '/qna/' + id,
      data: {
        "_token": "{{ csrf_token() }}",
        qid: id,
      },
      success: function(result){
        $('p#questionValue').remove();
        $('button#editQuestion').remove();
        $('button#deleteQuestion').remove();

        var p = document.createElement('p');
        p.innerHTML = result['value'];
        p.setAttribute('id', 'questionValue');
        if(selected != result['qid']) {
          selected = result['qid'];
          document.getElementById('ques_'+result['qid']).appendChild(p);

          // 수정버튼 : <button type="button" id="editQuestion">
          var editBtn = document.createElement('button');
          editBtn.innerHTML = '수정';
          editBtn.setAttribute('id', 'editQuestion');
          document.getElementById('option_'+result['qid']).appendChild(editBtn);

          // 삭제버튼 : <button type="button" id="deleteQuestion">
          var deleteBtn = document.createElement('button');
          deleteBtn.innerHTML = '삭제';
          deleteBtn.setAttribute('id', 'deleteQuestion');
          document.getElementById('option_'+result['qid']).appendChild(deleteBtn);
        } else {
          selected = -1;
        }
      },
      error: function(request, status, error){
        console.log("code:"+request.status+"\n"+"message:"+request.responseText+"\n"+"error:"+error);
      }
    });
  }
});
</script>
@stop