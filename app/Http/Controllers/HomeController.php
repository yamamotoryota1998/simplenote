<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Memo;
use App\Models\Tag;
use App\Models\Image;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        //ログインしているユーザーの定義
        $user = \Auth::user();
        //ユーザーごとのメモを取得取得
        $memos=Memo::where('user_id',$user['id'])->where('status',1)->orderBy('updated_at','DESC')->get();
        return view('home',compact('user','memos',));
    }

    public function create()
    {
        //ログインしているユーザー情報をviewに渡す
        $user = \Auth::user();
        //ユーザーごとのメモの内容取得
        $memos=Memo::where('user_id',$user['id'])->where('status',1)->orderBy('updated_at','DESC')->get();
        return view('create',compact('user','memos',));
    }

    public function store(Request $request)
    {
        $data=$request->all();
        // dd($data);
        
        //タグをインポート
        $tag= new Tag;
        $tag->name=$request->name;
        $tag->user_id=$request->user_id;
        $tag->save();
        //タグのIDが判明
        $memo=new Memo;
        $memo->content = $request->content;
        $memo->user_id = $request->user_id;
        $memo->tag_id = $tag->id;
        $memo->status = 1;
        $memo->save();
        
        $dir = 'sample';

        // if ($image=true){
        // //画像の元の名前を取得
        // $file_name = $request->file('image')->getClientOriginalName();
        // // 画像フォームでリクエストした画像をstorage > public > sample配下に画像を保存
        // $request->file('image')->storeAs('public/' . $dir, $file_name);
        // //画像をDBに入れる処理
        // $image = new Image();
        // $image->name = $file_name;
        // $image->path = 'storage/' . $dir . '/' . $file_name;
        // $image->save();
        // }
        // リダイレクト処理
        return redirect()->route('home');
    }
    
    public function edit($id){
        // 該当するIDのメモをデータベースから取得
        $user = \Auth::user();
        $memo = Memo::where('status', 1)->where('id', $id)->where('user_id', $user['id'])
        ->first();
        $memos=Memo::where('user_id',$user['id'])->where('status',1)->orderBy('updated_at','DESC')->get();
        $tags=Tag::where('user_id',$user['id'])->get();
        //   dd($memo);
        //取得したメモをViewに渡す
        return view('edit',compact('memo','user','memos','tags'));
    }

    public function update(Request $request, $id)
    {
        $inputs = $request->all();
        // dd($inputs);
        Memo::where('id', $id)->update(['content' => $inputs['content'],'tag_id' => $inputs['tag_id']]);
        return redirect()->route('home');
    }

    public function delete(Request $request, $id)
    {
        $inputs = $request->all();
        // dd($inputs);
        // 論理削除なので、status=2
        Memo::where('id', $id)->update([ 'status' => 2 ]);
        // ↓は物理削除
        // Memo::where('id', $id)->delete();

        return redirect()->route('home')->with('success', 'メモの削除が完了しました！');
    }
}
