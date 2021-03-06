<?php
	Class Model_board extends Model{

		function view(){
			$view = $this->fetch("select * from board where idx='{$this->param->idx}'");
			access($view == "","잘못된 접근입니다.");
			return $view;
		}

		function reply(){
			return $this->fetchAll("select * from reply where bidx='{$this->param->idx}' order by date desc");
		}

		function prev(){
			$prev = (array)$this->fetch("select max(idx) from board where idx < '{$this->param->idx}'");
			return $prev['max(idx)'];
		}

		function next(){
			$next = (array)$this->fetch("select min(idx) from board where idx > '{$this->param->idx}'");
			return $next['min(idx)'];
		}

		function boardAction(){
			$add_sql = $cancel = "";
			$table = 'board';
			if (isset($_POST['action'])) {
				loginChk();
				$_POST = array_map("htmlspecialchars", $_POST);
				extract($_POST);
				$msg = "완료되었습니다.";
				$url = "/";
				switch ($_POST['action']) {
					case 'insert':
						access(trim($name) == "" || trim($subject) == "" || trim($category) == "" || trim($content) == "","빈 값이 있습니다.");
						access($name != $_SESSION['member']['name'],"작성자를 변경하지 마세요.");
						$cat = ['영화','스포츠','게임','IT'];
						access(!in_array($category, $cat),"카테고리를 변경하지 마세요");
						if($_FILES['img']['type'] != ""){
							$type = $_FILES['img']['type'];
							$arr = ['image/jpg','image/jpeg','image/png','image/gif'];
							access(!in_array($type, $arr),"이미지만 업로드 할 수 있습니다.");
							$upload = _PUBLIC."upload/";
							$imgName = $_FILES['img']['name'];
							$target = $upload.$imgName;
							if(move_uploaded_file($_FILES['img']['tmp_name'], $target) ) {
								$add_sql .= ",img='{$imgName}'";
							}
						}
						$add_sql .= ",date=now();";
					break;
					case 'update':
						access(trim($subject) == "" || trim($content == ""),"빈 값이 있습니다.");
						if($_FILES['img']['type'] != ""){
							$type = $_FILES['img']['type'];
							$arr = ['image/jpg','image/jpeg','image/png','image/gif'];
							access(!in_array($type, $arr),"이미지만 업로드 할 수 있습니다.");
							$upload = _PUBLIC."upload/";
							$imgName = $_FILES['img']['name'];
							$target = $upload.$imgName;
							if(move_uploaded_file($_FILES['img']['tmp_name'], $target) ) {
								$add_sql .= ",img='{$imgName}'";
							}
						}
						$add_sql .= " where idx='{$this->param->idx}'";
					break;
					case 'delete':
						$add_sql = " where idx='{$this->param->idx}'";
					break;
					case 'reply_insert':
						access($_SESSION['member']['name'] != $name,"작성자를 임의로 변경하지 마세요.");
						$table = 'reply';
						$add_sql .= ", bidx='{$this->param->idx}'";
						$_POST['action'] = 'insert';
					break;
					case 'reply_delete':
						$add_sql = " where idx='{$idx}'";
						$_POST['action'] = 'delete';
						break;
				}
				$cancel .= "idx/action/table";
				$column = $this->getColumn($_POST,$cancel).$add_sql;
				$res = $this->setQuery($table,$_POST['action'],$column);
				access($res,$msg,$url);
			}
		}

	}