// ==========================================================================================
// LOGIN
// ==========================================================================================
// LOGIN: SHOW MENU
function menuLogin() {
	var elem = document.getElementsByClassName("loginMenu")[0];
	if (elem.hasAttribute("style")) elem.removeAttribute("style"); // если открыто - закрываем
	else elem.style.display = "block";
}
// LOGIN: API REQUEST > {err:0}
function entr(id) {
	event.preventDefault(); // почему то без этого отправляет POST от формы, а не от JS
	var mailID = document.getElementsByName("login_m")[0];
	var passID = document.getElementsByName("login_p")[0];
	var params = {"name" : id.name, "value" : mailID.value,"login_p":passID.value};
	if(checkMail(mailID)) AjaxPost(id,params,loginResp);
	else showErr(id,'введите вашу почту',"fade");
}
// LOGIN: API RESPONSE
function loginResp(id,jsObj) {
	if (jsObj.err == 0) location.reload();
	else showErr(id,jsObj.err_msg,"fade");
}
// ==========================================================================================
// REGISTRATION
// ==========================================================================================
// REGISTRATION: CHECK PASSWORD
function chkp(id) {
	checkPassword(id)
	if (chkAll(3)) buttonLastSwitch(true);
	else buttonLastSwitch(false);
}
// REGISTRATION: API REQUEST - CHECK NAME IF EXIST
function chkn(id) {
	var patt = /^[\w\sа-яА-Я]+$/i; 
	var minLen = 4;
	var maxLen = 25;
	if(id.value.length ==0) {
		showErr(id);
		id.removeAttribute("class");
	}
	else if(patt.exec(id.value)===null) {
		showErr(id,'имя должно быть из букв и цифер');
		id.setAttribute("class", "input_err");
	}
	else if(id.value.length < minLen) {
		showErr(id,'имя должно быть больше ' + minLen + ' символов');
		id.setAttribute("class", "input_err");
	}
	else if(id.value.length > maxLen) {
		showErr(id,'имя должно быть меньше ' + maxLen + ' символов');
		id.setAttribute("class", "input_err");
	}
	else {
		var params = {"name" : id.name,"value" : id.value};
		AjaxPost(id,params,checkInput);
	}
	if (chkAll(3)) buttonLastSwitch(true);
	else buttonLastSwitch(false);
}
// REGISTRATION: API REQUEST - CHECK MAIL IF EXIST > {"err":1,"err_msg":"такое имя занято"}
function chkm(id) {
	var params = {"name" : id.name,"value" : id.value};
	if(checkMail(id)) AjaxPost(id,params,checkInput);
	if (chkAll(3)) buttonLastSwitch(true);
	else buttonLastSwitch(false);
}
// REGISTRATION: API RESPONSE > Подсветка input
function checkInput(id,jsObj) {
	if (jsObj.err == 0) {
		id.setAttribute("class", "input_ok");
		showErr(id);
	}
	else {
		id.setAttribute("class", "input_err");
		showErr(id,jsObj.err_msg);
	}
	if (chkAll(3)) buttonLastSwitch(true);
	else buttonLastSwitch(false);
}
// REGISTRATION: LAST BUTTON SWITCH
function buttonLastSwitch(onn) {
	var countInputs = document.getElementById("acc").getElementsByTagName("input").length;
	var elem = document.getElementById("acc").getElementsByTagName("input")[countInputs-1];
	if (onn) elem.disabled = false;
	else elem.disabled = true;
}
// ==========================================================================================
// PASSWORD CHANGE
// ==========================================================================================
// PASSWORD CHANGE: OPEN FORM
function openPass(onn) {
	if (onn===1) var formPass = 
	'<div><input name="acc_pnew1" type="password" placeholder="Новый пароль" autocomplete="off" onkeyup="chknp(this)" autofocus></div>' +
	'<div><input name="acc_pnew2" type="password" placeholder="Подтвердить пароль" autocomplete="off" onkeyup="chknpp()"></div>' +
	'<div><input value="Отмена" type="button" onclick="openPass(0)"></div>';
	else var formPass = '<input value="Изменить пароль" type="button" onclick="openPass(1)">';
	document.getElementById("pchange").innerHTML = formPass;
}
// PASSWORD CHANGE: CHECK FILD ONE
function chknp(id) {
	checkPassword(id);
	chknpp(id);
}
// PASSWORD CHANGE: CHECK MATCH FILD TWO
function chknpp() {
	var elem1 = document.getElementsByName("acc_pnew1")[0];
	var elem2 = document.getElementsByName("acc_pnew2")[0];
	if (elem1.value==elem2.value) {
		showErr(elem2);
		classCopy = elem1.classList.item(0);
		if (classCopy==null) elem2.removeAttribute("class");
		else elem2.setAttribute("class", classCopy);
	}
	else {
		showErr(elem2,'пароли должены совпадать');
		elem2.setAttribute("class", "input_err");
	}
	if (chkAll(2)) buttonLastChange(true);
	else buttonLastChange(false);
}
// PASSWORD CHANGE: LAST BUTTON
function buttonLastChange(onn) {
	var countInputs = document.getElementById("acc").getElementsByTagName("input").length;
	var elem = document.getElementById("acc").getElementsByTagName("input")[countInputs-1];
	if (onn) {
		elem.value = 'Изменить пароль';
		elem.setAttribute("onclick", "changePass()");
	}
	else {
		elem.value = 'ОТМЕНА';
		elem.setAttribute("onclick", "openPass(0)");
	}
}
// PASSWORD CHANGE: API REQUEST > {"ok":"ok"}
function changePass() {
	event.preventDefault(); // почему то без этого отправляет POST от формы, а не от JS
	var msgID = document.getElementById("acc").getElementsByClassName("err")[2];
	var pass1 = document.getElementsByName("acc_pnew1")[0].value;
	var pass2 = document.getElementsByName("acc_pnew2")[0].value;
	var params = {"name":"acc_pnew", "value":pass1, "acc_pold":"oldpass"}; // Нет ввода старого пароля - сделать бы
	if (pass1==pass2) AjaxPost(msgID,params,chpResp);
	else msgID.innerHTML = 'Пароли не совпадают';
}
// PASSWORD CHANGE: API RESPONSE
function chpResp(id,jsObj) {
	if (jsObj.ok == 'ok') {
		showErr(id,'<b>пароль успешно изменен</b>'); // Куда то надо выводить
		openPass(0);
	}
	// Ошибка в данном случае не выводится
}
// ==========================================================================================
// COMMENTS
// ==========================================================================================
// COMMENTS: CHECK
function chkComment(id) {
	var patt = /[^\w\sа-яА-Я]/i; // можно запихнуть в конфиг
	if(patt.exec(id.value)!==null) {
		showErr(id,'Недопустимые символы','fade');
		id.setAttribute("class", "input_err");
		return false;
	}
	id.removeAttribute("class");
	return true;
}
// COMMENTS: SHOW
function comment(content_id) {
	var elem = document.getElementById(content_id).getElementsByClassName("blockC")[0];
	if (elem.hasAttribute("style")) elem.removeAttribute("style"); // если открыто - закрываем
	else {
		elem.style.display = "block";
		moreC(content_id);
	}
}
// COMMENTS: API REQUEST - MORE > resp {0[],1[],2[],rows_count,rows_limit,rows_from}
function moreC(content_id) {
	var elem = document.getElementById(content_id);
	var fromRow = window.event.target.name || elem.getElementsByClassName("blockC")[0].getAttribute("name") || 0; // Если запрос от API тогда с поля blockC
	var params = {"name":"comment", "value":content_id, "nrow":fromRow};
	AjaxPost(elem,params,buildComments);
	refrC(content_id);
}
// COMMENTS: API REQUEST - PUT(ADD) > resp {ok:ok,cid:123}
function putC(content_id) {
	var elemInput = document.getElementById(content_id).getElementsByTagName("textarea")[0];
	var params = {"name":"add_comment", "value":content_id, "comment_txt":elemInput.value};
	if (chkComment(elemInput)) {
		AjaxPost(content_id,params,actionComment);
		elemInput.value = ""; // Очищаем инпут поле(само нет), можно поп-сообщение какое сделать
	}
}
// COMMENTS: API REQUEST - DELETE > resp {ok:ok,cid:123}
function delC(comment_id) {
	var content_id = window.event.target.name;
	var params = {"name":"del_comment", "value":content_id, "comment_id":comment_id};
	AjaxPost(content_id,params,actionComment);
}
// COMMENTS: API REQUEST - REFRESH > resp {found:1,rows_count:123}
function refrC(content_id) {
	var elem = document.getElementById(content_id).getElementsByClassName("likeline")[0].getElementsByTagName("span")[0]; // Блок значка коментов
	var params = {"name":"comment_refr", "value":content_id};
	AjaxPost(elem,params,refreshComments);
}
// COMMENTS: API RESPONSE - ACTION DONE > THEN - REQUEST: moreC()
function actionComment(id,jsObj) {
	if(jsObj.ok=='ok') moreC(jsObj.cid);
	else moreC(id);
}
// COMMENTS: API RESPONSE - REFRESH count,author flag
function refreshComments(id,jsObj) {
	var ico = (jsObj.found==1) ? 'ico on' : 'ico';
	id.getElementsByTagName("img")[0].className = ico; // Подсветка коментов
	id.getElementsByTagName("span")[0].innerHTML = jsObj.rows_count; // Кол во коментов
}
// COMMENTS: API RESPONSE - BUILDER в виде HTML - Топорный метод
function buildComments(id,jsObj) {
	var i = 0;
	var txt = '';
	var content_id = id.getAttribute("id");
	var idMsg = id.getElementsByClassName("comments")[0];
	if (!jsObj) return false;
	while (jsObj[i]) {
		var deleteButton = ''; // Кнопка удаления комента
		if(jsObj[i]['can_del']==1) deleteButton = '<span><a onclick="delC(' + jsObj[i]['cmm_id'] + ')" name="' + content_id + '">X</a></span>';
		jsObj[i]['img'] = jsObj[i]['img'] || 'blank'; // пустые аватарки заменяем blank
		txt += '<div>'+ showTime(jsObj[i]['cr_date']);
		txt += '<a href="/acc/' + jsObj[i]['user_id'] + '">' + '<img src="/' + jsObj[i]['img'] + '.png" class="avas">' + jsObj[i]['name'] + '</a>'
		txt += ':&nbsp;' + jsObj[i]['msg'] + deleteButton + '</div>';
		i++;
	}
	txt += navigationBar('moreC',content_id,jsObj.rows_count,jsObj.rows_limit,jsObj.rows_from,i);
	idMsg.innerHTML = txt;
}
// ------------------------------------------------------------------------------------------
// NAVIGATION BAR (onclick mthd, onclick id, count rows, rows on one page, start row, count of responsed rows, dist до кнопок прокрутки)
function navigationBar(mthd,id,countR,limitR,startR,countRespR,dist) { // Значения приходят как строки !! 
	var i = 0;
	var txt = '';
	// НАСТРОЙКИ
	var countP = (countR - countR%limitR)/limitR; // Кол-во страниц
	if(countR%limitR>0) countP++;
	var endR = parseInt(startR) + countRespR; // Позиция последний строки из запроса
	var currentP = (endR - endR%limitR)/limitR; // Текущая страница
	if(startR%limitR>0) currentP++;
	// Через какое растояние показываем кнопки прокрутки
	var dist = dist || 2;
	var startDist = currentP - dist;
	var endDist = currentP + dist;
	// Рисуем NAV BAR
	if(countP > 1) {
		txt = '<div class="navC">';
		if(startDist>1) txt += '<a onclick="' + mthd + '(' + id + ')" name="0">&laquo;</a>'; // Прокрутка в начало
		else startDist = 1;
		if(endDist>countP) endDist = countP;
		for (i = startDist; i < endDist+1; i++) {
			var fromName = (i*limitR<countR) ? i*limitR-limitR : countR-limitR; // Чтоб на последней странице было limitR записей
			if (currentP == i) {
				txt += '<a class="on">' + i + '</a>'; // Подсвечиваем текущую страницу
				// Пока эта строчка тут - не убирать в fu.js
				document.getElementById(id).getElementsByClassName("blockC")[0].setAttribute("name",fromName); // Выставляем страницу - для поиска - в данной функции неясно...
			}
			else txt += '<a onclick="' + mthd + '(' + id + ')" name="' + fromName + '">' + i + '</a>';
		}
		if(endDist<countP) txt += '<a onclick="' + mthd + '(' + id + ')" name="' + (countR - limitR) + '">&raquo;</a>'; // Прокрутка в конец
		txt += '</div>';
	}
	return txt;
}
// ==========================================================================================
// LIKE
// ==========================================================================================
// LIKE: API REQUEST - PUT LIKE - {ok:dlike,like:123,dlike:123}
function like(content_id) {
	var id = window.event.target.parentNode;
	var params = {"name":"like", "value":content_id};
	AjaxPost(id,params,refreshLike);
}
// LIKE: API REQUEST - PUT DIS LIKE - {ok:dlike,like:123,dlike:123}
function dlike(content_id) {
	var id = window.event.target.parentNode;
	var params = {"name":"like", "value":content_id, "dis":"yes"};
	AjaxPost(id,params,refreshLike);
}
// LIKE: API RESPONSE - REFRESH LIKE
function refreshLike(whr,jsObj) {
	whr.getElementsByTagName("img")[0].className = (jsObj.ok=='like') ? "ico on" : "ico";
	whr.getElementsByTagName("img")[1].className = (jsObj.ok=='dlike') ? "ico on" : "ico";
	whr.getElementsByTagName("span")[0].innerHTML = jsObj.like;
	whr.getElementsByTagName("span")[1].innerHTML = jsObj.dlike;
}
