<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2018/10/11
 * Time: 20:17
 */

namespace App\Admin\Extensions;

use Encore\Admin\Form\Field;

class Map extends Field
{
    protected $view = 'admin.map';

    public function render()
    {
        $this->script = <<<EOT

var container = document.getElementById("container");
    var map = new qq.maps.Map(container, {
            zoom: 12

        }),
        label = new qq.maps.Label({
            map: map,
            offset: new qq.maps.Size(15,-12),
            draggable: false,
            clickable: false
        }),
        markerArray = [],
        btnSearch = document.getElementById("btn_search"),
        bside = document.getElementById("bside_left"),
        curCity = document.getElementById("cur_city"),
        url, query_city,
        cityservice = new qq.maps.CityService({
            complete: function (result) {
                curCity.children[0].innerHTML = result.detail.name;
                map.setCenter(result.detail.latLng);
            }
        });
    cityservice.searchLocalCity();
    map.setOptions({
        draggableCursor: "crosshair"
    });

    $(container).mouseenter(function () {
        label.setMap(map);
    });
    $(container).mouseleave(function () {
        label.setMap(null);
    });

    qq.maps.event.addListener(map, "mousemove", function (e) {
        var latlng = e.latLng;
        label.setPosition(latlng);
        label.setContent(latlng.getLat().toFixed(6) + "," + latlng.getLng().toFixed(6));
    });
    //所有的key换成自己的key不然没有效果会报错
    var url3;
    qq.maps.event.addListener(map, "click", function (e) {
        document.getElementById("latitude").value = e.latLng.getLat().toFixed(6);
        document.getElementById("longitude").value = e.latLng.getLng().toFixed(6);
    });

    qq.maps.event.addListener(map, "zoom_changed", function () {
        document.getElementById("level").innerHTML = "当前缩放等级：" + map.getZoom();
    });
    var listener_arr = [];
    var isNoValue = false;
    qq.maps.event.addDomListener(btnSearch, 'click', function () {
        var value = this.parentNode.getElementsByTagName("input")[0].value;
        var latlngBounds = new qq.maps.LatLngBounds();
        for(var i= 0,l=listener_arr.length;i<l;i++){
            qq.maps.event.removeListener(listener_arr[i]);
        }
        listener_arr.length = 0;
        query_city = curCity.children[0].innerHTML;
        url = encodeURI("https://apis.map.qq.com/ws/place/v1/search?keyword=" + value + "&boundary=region(" + query_city + ",0)&page_size=9&page_index=1&key=SQHBZ-ESIWX-Y264H-7BJAO-GTM6K-YHFJO&output=jsonp&&callback=?");
        $.getJSON(url, function (result) {
            if (result.count) {
                isNoValue = false;
                bside.innerHTML = "";
                each(markerArray, function (n, ele) {
                    ele.setMap(null);
                });
                markerArray.length = 0;
                each(result.data, function (n, ele) {
                    var latlng = new qq.maps.LatLng(ele.location.lat, ele.location.lng);
                    latlngBounds.extend(latlng);
                    var left = n * 27;
                    var marker = new qq.maps.Marker({
                        map: map,
                        position: latlng,
                        zIndex: 10
                    });
                    marker.index = n;
                    marker.isClicked = false;
                    setAnchor(marker, true);
                    markerArray.push(marker);
                    var listener1 = qq.maps.event.addDomListener(marker, "mouseover", function () {
                        var n = this.index;
                        setCurrent(markerArray, n, false);
                        setCurrent(markerArray, n, true);
                        label.setContent(this.position.getLat().toFixed(6) + "," + this.position.getLng().toFixed(6));
                        label.setPosition(this.position);
                        label.setOptions({
                            offset: new qq.maps.Size(15, -20)
                        })
    
                    });
                    listener_arr.push(listener1);
                    var listener2 = qq.maps.event.addDomListener(marker, "mouseout", function () {
                        var n = this.index;
                        setCurrent(markerArray, n, false);
                        setCurrent(markerArray, n, true);
                        label.setOptions({
                            offset: new qq.maps.Size(15, -12)
                        })
                    });
                    listener_arr.push(listener2);
                    var listener3 = qq.maps.event.addDomListener(marker, "click", function () {
                        var n = this.index;
                        setFlagClicked(markerArray, n);
                        setCurrent(markerArray, n, false);
                        setCurrent(markerArray, n, true);
                    });
                    listener_arr.push(listener3);
                    map.fitBounds(latlngBounds);
                    var div = document.createElement("div");
                    div.className = "info_list";
                    var order = document.createElement("div");
                    var leftn = -54 - 17 * n;
                    order.style.cssText = "width:17px;height:17px;margin:3px 3px 0px 0px;float:left;background:url(https://lbs.qq.com/tool/getpoint/img/marker_n.png) " + leftn + "px 0px";
                    div.appendChild(order);
                    var pannel = document.createElement("div");
                    pannel.style.cssText = "width:200px;float:left;";
                    div.appendChild(pannel);
                    var name = document.createElement("p");
                    name.style.cssText = "margin:0px;color:#0000CC";
                    name.innerHTML = ele.title;
                    pannel.appendChild(name);
                    var address = document.createElement("p");
                    address.style.cssText = "margin:0px;";
                    address.innerHTML = "地址：" + ele.address;
                    pannel.appendChild(address);
                    if (ele.tel != undefined) {
                        var phone = document.createElement("p");
                        phone.style.cssText = "margin:0px;";
                        phone.innerHTML = "电话：" + ele.tel;
                        pannel.appendChild(phone);
                    }
                    var position = document.createElement("p");
                    position.style.cssText = "margin:0px;";
                    position.innerHTML = "坐标：" + ele.location.lat.toFixed(6) + "，" + ele.location.lng.toFixed(6);
                    pannel.appendChild(position);
                    bside.appendChild(div);
                    div.style.height = pannel.offsetHeight + "px";
                    div.isClicked = false;
                    div.index = n;
                    marker.div = div;
                    div.marker = marker;
                });
                $("#bside_left").delegate(".info_list", "mouseover", function (e) {
    
                    var n = this.index;
    
                    setCurrent(markerArray, n, false);
                    setCurrent(markerArray, n, true);
                });
                $("#bside_left").delegate(".info_list", "mouseout", function () {
                    each(markerArray, function (n, ele) {
                        if (!ele.isClicked) {
                            setAnchor(ele, true);
                            ele.div.style.background = "#fff";
                        }
                    })
                });
                $("#bside_left").delegate(".info_list", "click", function (e) {
                    var n = this.index;
                    setFlagClicked(markerArray, n);
                    setCurrent(markerArray, n, false);
                    setCurrent(markerArray, n, true);
                    map.setCenter(markerArray[n].position);
                });
            } else {
    
                bside.innerHTML = "";
                each(markerArray, function (n, ele) {
                    ele.setMap(null);
                });
                markerArray.length = 0;
                var novalue = document.createElement('div');
                novalue.id = "no_value";
                novalue.innerHTML = "对不起，没有搜索到您要找的结果!";
                bside.appendChild(novalue);
                isNoValue = true;
            }
        });
    });
    
    btnSearch.onmousedown = function () {
        this.className = "btn_active";
    };
    btnSearch.onmouseup = function () {
        this.className = "btn";
    };


    function setAnchor(marker, flag) {
        var left = marker.index * 27;
        if (flag == true) {
            var anchor = new qq.maps.Point(10, 30),
                origin = new qq.maps.Point(left, 0),
                size = new qq.maps.Size(27, 33),
                icon = new qq.maps.MarkerImage("https://lbs.qq.com/tool/getpoint/img/marker10.png", size, origin, anchor);
            marker.setIcon(icon);
        } else {
            var anchor = new qq.maps.Point(10, 30),
                origin = new qq.maps.Point(left, 35),
                size = new qq.maps.Size(27, 33),
                icon = new qq.maps.MarkerImage("https://lbs.qq.com/tool/getpoint/img/marker10.png", size, origin, anchor);
            marker.setIcon(icon);
        }
    }
    function setCurrent(arr, index, isMarker) {
        if (isMarker) {
            each(markerArray, function (n, ele) {
                if (n == index) {
                    setAnchor(ele, false);
                    ele.setZIndex(10);
                } else {
                    if (!ele.isClicked) {
                        setAnchor(ele, true);
                        ele.setZIndex(9);
                    }
                }
            });
        } else {
            each(markerArray, function (n, ele) {
                if (n == index) {
                    ele.div.style.background = "#DBE4F2";
                } else {
                    if (!ele.div.isClicked) {
                        ele.div.style.background = "#fff";
                    }
                }
            });
        }
    }

    function setFlagClicked(arr, index) {
        each(markerArray, function (n, ele) {
            if (n == index) {
                ele.isClicked = true;
                ele.div.isClicked = true;
                var latLng = ele.getPosition();
                document.getElementById("latitude").value = latLng.getLat().toFixed(6);
                document.getElementById("longitude").value = latLng.getLng().toFixed(6);
            } else {
                ele.isClicked = false;
                ele.div.isClicked = false;
            }
        });
    }

    function each(obj, fn) {
        for (var n = 0, l = obj.length; n < l; n++) {
            fn.call(obj[n], n, obj[n]);
        }
    }

EOT;
        return parent::render();

    }
}