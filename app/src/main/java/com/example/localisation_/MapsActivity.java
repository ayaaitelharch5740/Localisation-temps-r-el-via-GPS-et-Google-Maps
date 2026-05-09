package com.example.localisation_;

import android.os.Bundle;

import androidx.fragment.app.FragmentActivity;

import com.android.volley.Request;
import com.android.volley.RequestQueue;
import com.android.volley.toolbox.JsonObjectRequest;
import com.android.volley.toolbox.Volley;
import com.google.android.gms.maps.GoogleMap;
import com.google.android.gms.maps.OnMapReadyCallback;
import com.google.android.gms.maps.SupportMapFragment;
import com.google.android.gms.maps.model.LatLng;
import com.google.android.gms.maps.model.MarkerOptions;

import org.json.JSONArray;
import org.json.JSONObject;

public class MapsActivity extends FragmentActivity implements OnMapReadyCallback {

    private GoogleMap mMap;
    private RequestQueue requestQueue;

    private final String showUrl =
            "http://192.168.43.228/localisation/showPositions.php";

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_maps);

        requestQueue = Volley.newRequestQueue(getApplicationContext());

        SupportMapFragment mapFragment =
                (SupportMapFragment) getSupportFragmentManager()
                        .findFragmentById(R.id.map);

        if (mapFragment != null) {
            mapFragment.getMapAsync(this);
        }
    }

    @Override
    public void onMapReady(GoogleMap googleMap) {
        mMap = googleMap;
        setUpMap();
    }

    private void setUpMap() {

        JsonObjectRequest request = new JsonObjectRequest(
                Request.Method.POST,
                showUrl,
                null,

                response -> {
                    try {

                        JSONArray positions =
                                response.getJSONArray("positions");

                        for (int i = 0; i < positions.length(); i++) {

                            JSONObject position =
                                    positions.getJSONObject(i);

                            double lat =
                                    position.getDouble("latitude");

                            double lon =
                                    position.getDouble("longitude");

                            mMap.addMarker(
                                    new MarkerOptions()
                                            .position(new LatLng(lat, lon))
                                            .title("Marker")
                            );
                        }

                    } catch (Exception e) {
                        e.printStackTrace();
                    }
                },

                error -> error.printStackTrace()
        );

        requestQueue.add(request);
    }
}