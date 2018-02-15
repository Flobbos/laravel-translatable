<?php

namespace Flobbos\TranslatableDB\Middleware;

use Closure;

class LanguageIdentification{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next){
        if(config('translatabledb.use_db')){
            $lang_model = resolve(config('translatabledb.language_model'));
            $lang = $lang_model
                    ->where(config('translatabledb.locale_column'),app()->getLocale())
                    ->first();
            if(is_null($lang) && config('translatabledb.use_fallback')){
                $lang_id = config('translatabledb.fallback_locale_id');
            }
            else{
                $lang_id = $lang->id;
            }
        }
        else{
            if(!$lang_id = config('translatabledb.language_array.'.app()->getLocale().'.language_id')){
                $lang_id = config('translatabledb.fallback_locale_id'); 
            }
        }
        
        $request->attributes->add(['language_id'=>$lang_id]);
        return $next($request);
    }
}