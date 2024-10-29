<?php
class amumu_paging { 

  var $total, $page, $size, $scale; 
  var $start_page, $page_max, $offset, $block, $tails; 
  var $prev_block, $next_block;

  function amumu_paging( $total, $page, $arr='', $size='10', $scale='10' ) { 
    $this->total 			= $total; //게시물 전체개수 
    $this->page 			= $page; //페이지번호 
    $this->size 			= $size; //목록개수 
    $this->scale 			= $scale; //페이지개수
	$start_page = "";
	$tails = "";
    $this->start_page = $start_page; //페이지 시작번호 
    $this->page_max 	= ceil( $total / $size ); //총 페이지개수 
    $this->offset 		= ( $page - 1 ) * $size; //해당 페이지에서 시작하는 목록번호 
    $this->block 			= floor( ( $page - 1 ) / $scale ); //페이지를 10개씩보여준다면 1~10페이지까지는 0블럭.. 
    $this->no 				= $this->total - $this->offset; //목록에서 번호나열할때 필요.. (하단 사용법을보세요..) 
    if ( is_array( $arr ) ) {
      while ( list( $key, $val ) = each( $arr ) ) {
        if( $key != 'bookmark' ) $tails .= $key.'='.$val.'&'; 
      }
      $this->tails = substr( $tails, 0, -1 ); 
    }
  } 

  function amumu_get_paging() { 
	  $result ="";
    if( $this->total > $this->size ) { 
      //if($this->page==1) $result .= '<a class="prv"><span>&lt;</span></a>'; 
      //else $result .= '<a href="?'.$this->tails.'&this_page2=1" class="prv"><span>&lt;</span></a>';
      if( $this->block > 0 ) { 
	      $prev_block = ( $this->block - 1 ) * $this->scale + 1; 
	      $result .= '<a href=?'.$this->tails.'&this_page='.$prev_block.' class="prv"><</a>'; 
      }
      $this->start_page = $this->block * $this->scale + 1; 
      for($i = 1; $i <= $this->scale && $this->start_page <= $this->page_max; $i++, $this->start_page++) { 
        if($this->start_page == $this->page) $class = 'active';
        else $class = '';
        $result.= '<a href=?'.$this->tails.'&this_page='.$this->start_page.'>'.$this->start_page.'</a>'; 
      } 
      if($this->page_max > ($this->block + 1) * $this->scale) { 
        $next_block = ($this->block + 1) * $this->scale + 1; 
        $result .='<a href=?'.$this->tails.'&this_page='.$next_block.' class="nxt">>></a>'; 
      }
      //if($this->page==$this->page_max) $result .= '<a class="nxt"><span>&lt;</span></a>'; 
      //else $result .= '<a href="?'.$this->tails.'&this_page='.$this->page_max.'" class="nxt"><span>&lt;</span></a>';
    }
    return $result; 
  }

}//class end 

?>