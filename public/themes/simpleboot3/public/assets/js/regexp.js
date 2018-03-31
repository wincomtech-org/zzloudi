//验证手机号;
function is_mobile(tel){
	var reg=/^[1][3,4,5,6,7,8][0-9]{9}$/; 
	return reg.test(tel);
}
//验证密码格式（必须字母+数字组合）;
function is_password(str){
	var reg=/^[a-zA-Z0-9]{6,18}$/;
	return reg.test(str);
}
//验证邮箱格式是否正确；
function is_email(tr){
	var reg=/^\w+((-\w+)|(\.\w+))*\@[A-Za-z0-9]+((\.|-)[A-Za-z0-9]+)*\.[A-Za-z0-9]+$/;
	return reg.test(tr);
}