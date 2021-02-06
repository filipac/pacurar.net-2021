@unless(post_password_required())
    @php
        $get_comments = get_comments('status=approve&post_id=' . get_post()->ID);
        $byType = separate_comments($get_comments);
    @endphp
    @if(count($byType['pings']) > 0)
    <div class="bg-green-200 shadow-box flex-1 w-full border-2 border-black px-2 py-2 text-xl mb-6">
        @if(ICL_LANGUAGE_CODE == 'ro')
        <h3>Ping(pong)-uri la acest articol</h3>
        @else
        <h3>Ping(pongs) to this article</h3>
        @endif

    <div class="prose max-w-none">
        <ul>
        @foreach($byType['pings'] as $comm)
        @php
            $GLOBALS['comment']       = $comm;
        @endphp
<li id="comment-{{ $comm->comment_ID }}" {!! comment_class( '', $comm) !!}>
			<div class="comment-body">
				{!! get_comment_author_link_blank( $comm ) !!} {!! edit_comment_link( __( 'Edit' ), '<span class="edit-link">', '</span>' ) !!}
			</div>
</li>
        @endforeach
        </ul>
    </div>
    </div>

    @endif
    @if(count($byType['comment']) > 0)
<h3 id="comments">
    @if(1 == $byType['comment'])
    @if(ICL_LANGUAGE_CODE == 'ro')
        Acest articol are doar un comentariu.
        @else
        This article has a single comment.
        @endif
    @else
        {{ ($byType['comment'] == 1 ? (ICL_LANGUAGE_CODE == 'ro' ? '1 comentariu la acest articol' : '1 comment to this article'): count($byType['comment']).(ICL_LANGUAGE_CODE == 'ro' ? ' comentarii la acest articol' : ' comments to this article')) }}
    @endif
</h3>

    <div class="navigation">
        <div class="alignleft">{!! previous_comments_link() !!}
        </div>
        <div class="alignright">{!! next_comments_link() !!}
        </div>
    </div>
    <ol class="commentlist">
        {!! wp_list_comments(['type' => 'comment']) !!}
    </ol>
    <div class="navigation">
        <div class="alignleft">{!! previous_comments_link() !!}
        </div>
        <div class="alignright">{!! next_comments_link() !!}
        </div>
    </div>

    @else

    @if (comments_open())
@else
<!-- If comments are closed. -->
<p class="nocomments">{{ _e('Comments are closed.') }}
</p>

@endif

    @endif

    @php wp_enqueue_script( 'comment-reply' ); @endphp

    {!! comment_form() !!}
@else
<p class="nocomments">
    @if(ICL_LANGUAGE_CODE == 'ro')
    Acest post are o parola, introdu parola (daca o sti, haha) ca sa vezi comentariile.
    @else
    This post is password protected, enter the password (if you know it, wink wink) to see the comments.
    @endif
</p>
@endunless
