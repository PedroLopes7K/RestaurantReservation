<?php

namespace App\Http\Controllers\Admin;

use App\Enums\TableStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\ReservationStoreRequest;
use App\Models\Reservation;
use App\Models\Table;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ReservationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

        $reservations = Reservation::all();
        return view('admin.reservations.index', compact('reservations'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {

        $tables = Table::where('status', TableStatus::Disponivel)->get();
        return view('admin.reservations.create', compact('tables'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(ReservationStoreRequest $request)
    {

        $table = Table::findOrFail($request->table_id);

        if ($request->guest_number > $table->guest_number) {
            return back()->with('warning', 'Pessoas em excesso para essa mesa!');
        }

        $request_date = Carbon::parse($request->reservation_date);

        foreach ($table->reservations as $res) {

            if ($res->reservation_date == $request_date) {
                return back()->with('warning', 'Mesa já reservada para essa data!');
            }
        }

        Reservation::create($request->validated());

        return to_route('admin.reservations.index')->with('success', 'Reserva criada com sucesso!');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Reservation $reservation)
    {
        $tables = Table::where('status', TableStatus::Disponivel)->get();
        return view('admin.reservations.edit', compact('reservation', 'tables'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(ReservationStoreRequest $request, Reservation $reservation)
    {
        $table = Table::findOrFail($request->table_id);
        if ($request->guest_number > $table->guest_number) {
            return back()->with('warning', 'Escolha uma mesa com capacidade suficiente.');
        }
        $request_date = Carbon::parse($request->res_date);
        $reservations = $table->reservations()->where('id', '!=', $reservation->id)->get();
        foreach ($reservations as $res) {
            if ($res->reservation_date == $request_date) {
                return back()->with('warning', 'Essa mesa já esta reservada para essa data!');
            }
        }

        $reservation->update($request->validated());
        return to_route('admin.reservations.index')->with('success', 'Reserva atualizada com sucesso!');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Reservation $reservation)
    {
        $reservation->delete();

        return to_route('admin.reservations.index')->with('warning', 'Reserva deletada com sucesso!');
    }
}
