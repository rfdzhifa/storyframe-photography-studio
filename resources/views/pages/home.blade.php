@extends('app')

@section('title', 'Home')

@section('content')
    <div class="w-full h-[calc(100vh-5.5rem)] p-10 flex justify-center items-center gap-2.5"> 
        <div class="flex-1 self-stretch rounded-[60px] inline-flex flex-col justify-center items-center gap-2.5"
             style="background-image: url('{{ asset('assets/images/bg-hero-image.png') }}'); background-size: cover; background-position: center;">

            <div class="size- flex flex-col justify-start items-center gap-6">
                 <div class="size- py-3 flex flex-col justify-start items-start gap-5">
                    <div class="w-[627px] text-center justify-start text-white text-7xl font-medium font-['Inter_Tight']">CAPTURING MOMENTS FRAMING STORIES</div>
                    <div class="w-[627px] text-center justify-start text-neutral-100 text-lg font-normal font-['Inter_Tight']">Storyframe adalah studio fotografi profesional yang berdedikasi untuk menjadikan setiap momen Anda sebagai kisah visual yang tak terlupakan.</div>
                </div>
                <div class="h-12 px-8 py-2.5 bg-white rounded-[50px] inline-flex justify-center items-center gap-2.5">
                    <div class="justify-start text-black text-base font-medium font-['Inter_Tight']">BOOKING</div>
                </div>
            </div>
        </div>
    </div>
@endsection