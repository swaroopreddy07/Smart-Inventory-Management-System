import pandas as pd
import numpy as np
import matplotlib.pyplot as plt
from sklearn.preprocessing import MinMaxScaler
from tensorflow.keras.models import Sequential
from tensorflow.keras.layers import Dense, LSTM
from datetime import datetime, timedelta
import os

# Set environment variable to suppress TensorFlow warnings
os.environ['TF_CPP_MIN_LOG_LEVEL'] = '2'

# Create sample data and save to Excel if the data file doesn't exist
def create_sample_data(filename="consumption_data_2025.xlsx"):
    try:
        # First try to read the file
        df = pd.read_excel(filename)
        print(f"Found existing data file: {filename}")
        return df
    except FileNotFoundError:
        print(f"Creating sample data and saving to {filename}...")
        
        # Generate dates for 2025
        start_date = datetime(2025, 1, 1)
        end_date = datetime(2025, 12, 31)
        dates = [start_date + timedelta(days=i) for i in range((end_date - start_date).days + 1)]
        
        # Define items - using ASCII characters only to avoid encoding issues
        items = ['Potatoes', 'Chocolates', 'Paneer']
        
        # Define special days with peak factors
        special_days = {
            datetime(2025, 1, 1): 2,    # New Year
            datetime(2025, 3, 29): 3,   # Holi
            datetime(2025, 4, 13): 2,   # Vishu
            datetime(2025, 7, 15): 3,   # Eid
            datetime(2025, 8, 15): 2,   # Independence Day
            datetime(2025, 10, 2): 2,   # Gandhi Jayanti
            datetime(2025, 11, 1): 3,   # Diwali
            datetime(2025, 12, 25): 3   # Christmas
        }
        
        # Create the dataframe
        data = []
        for date in dates:
            for item in items:
                # Base consumption
                consumption = 50
                
                # Weekend boost
                if date.weekday() >= 5:  # Saturday or Sunday
                    consumption += 30
                
                # Special day boost
                if date in special_days:
                    consumption += 50 * special_days[date]
                
                # Add randomness
                consumption += np.random.randint(0, 20)
                
                data.append({
                    'item': item,
                    'consumed_quantity': int(consumption),  # Convert to int to avoid any floating point issues
                    'consumption_date': date
                })
        
        df = pd.DataFrame(data)
        df.to_excel(filename, index=False)
        return df

# Prepare data for LSTM model
def prepare_data(df, item_name=None, sequence_length=30):
    # Filter data for specific item or use total consumption
    if item_name:
        item_data = df[df['item'] == item_name].sort_values('consumption_date')
        time_series = item_data['consumed_quantity'].values
    else:
        # Group by date and sum consumption across all items
        daily_consumption = df.groupby('consumption_date')['consumed_quantity'].sum().reset_index()
        daily_consumption = daily_consumption.sort_values('consumption_date')
        time_series = daily_consumption['consumed_quantity'].values
    
    # Ensure we have enough data
    if len(time_series) <= sequence_length:
        raise ValueError(f"Not enough data points. Need more than {sequence_length} points, but got {len(time_series)}.")
    
    # Scale the data
    scaler = MinMaxScaler(feature_range=(0, 1))
    scaled_data = scaler.fit_transform(time_series.reshape(-1, 1))
    
    # Create sequences for LSTM
    X, y = [], []
    for i in range(len(scaled_data) - sequence_length):
        X.append(scaled_data[i:i + sequence_length, 0])
        y.append(scaled_data[i + sequence_length, 0])
    
    X, y = np.array(X), np.array(y)
    X = np.reshape(X, (X.shape[0], X.shape[1], 1))
    
    # Split data into train and test sets (80-20 split)
    train_size = int(len(X) * 0.8)
    X_train, X_test = X[:train_size], X[train_size:]
    y_train, y_test = y[:train_size], y[train_size:]
    
    return X_train, y_train, X_test, y_test, scaler, time_series

# Build and train LSTM model
def build_lstm_model(X_train, y_train, X_test, y_test):
    model = Sequential()
    model.add(LSTM(units=50, return_sequences=True, input_shape=(X_train.shape[1], 1)))
    model.add(LSTM(units=50))
    model.add(Dense(units=1))
    
    model.compile(optimizer='adam', loss='mean_squared_error')
    
    # Use try-except to handle potential Unicode errors during training
    try:
        history = model.fit(
            X_train, y_train, 
            epochs=20, 
            batch_size=32, 
            validation_data=(X_test, y_test),
            verbose=1
        )
    except UnicodeEncodeError:
        # If encoding error occurs, train with verbose=0 to avoid printing problematic characters
        print("Unicode error encountered. Training with reduced output...")
        history = model.fit(
            X_train, y_train, 
            epochs=20, 
            batch_size=32, 
            validation_data=(X_test, y_test),
            verbose=0
        )
        print("Training completed.")
    
    return model, history

# Predict next month's consumption
def predict_next_month(model, time_series, scaler, sequence_length=30):
    # Get the last sequence from our data
    last_sequence = time_series[-sequence_length:]
    last_sequence_scaled = scaler.transform(last_sequence.reshape(-1, 1))
    
    # Initialize prediction list with the last known sequence
    predictions = []
    current_batch = last_sequence_scaled.reshape(1, sequence_length, 1)
    
    # Predict next 30 days (roughly a month)
    for _ in range(30):
        # Generate prediction for the next step
        next_pred = model.predict(current_batch, verbose=0)[0, 0]
        predictions.append(next_pred)
        
        # Update the sequence with the new prediction (rolling window)
        # Remove first value and append the prediction
        current_batch = np.append(
            current_batch[:, 1:, :],  # All except first value
            np.array([[[next_pred]]]),  # New prediction reshaped to match dimensions
            axis=1
        )
    
    # Convert predictions to numpy array and reshape for inverse transform
    predictions_array = np.array(predictions).reshape(-1, 1)
    
    # Inverse transform to get back to original scale
    predictions_inverse = scaler.inverse_transform(predictions_array)
    
    # Convert to integers and return as flat array
    return np.round(predictions_inverse.flatten()).astype(int)

# Plot results
def plot_results(time_series, predictions, item_name=None):
    try:
        plt.figure(figsize=(12, 6))
        
        # Plot historical data
        plt.plot(range(len(time_series)), time_series, label='Historical Data')
        
        # Plot predictions
        plt.plot(range(len(time_series), len(time_series) + len(predictions)), 
                predictions, label='Predictions', color='red')
        
        plt.axvline(x=len(time_series), color='green', linestyle='--', label='Prediction Start')
        
        title = f'Consumption Prediction for {item_name}' if item_name else 'Total Consumption Prediction'
        plt.title(title)
        plt.xlabel('Days')
        plt.ylabel('Consumption Quantity')
        plt.legend()
        plt.grid(True)
        plt.tight_layout()
        
        # Save figure
        filename = f'{item_name}_prediction.png' if item_name else 'total_consumption_prediction.png'
        plt.savefig(filename)
        plt.close()
        print(f"Plot saved as {filename}")
    except Exception as e:
        print(f"Error creating plot: {e}")

def main():
    try:
        # Create or load data
        filename = "consumption_data_2025.xlsx"
        df = create_sample_data(filename)
        df['consumption_date'] = pd.to_datetime(df['consumption_date'])
        
        # Initialize predictions dataframes
        total_predictions = []
        item_predictions = []
        
        # Process total consumption first
        print("\nProcessing predictions for total consumption across all items")
        
        try:
            # Prepare data for total consumption
            X_train, y_train, X_test, y_test, scaler, time_series = prepare_data(df)
            
            # Build and train model
            model, history = build_lstm_model(X_train, y_train, X_test, y_test)
            
            # Predict next month
            predictions = predict_next_month(model, time_series, scaler)
            
            # Generate prediction dates
            last_date = df['consumption_date'].max()
            prediction_dates = [(last_date + timedelta(days=i+1)) for i in range(len(predictions))]
            
            # Store total predictions
            for i, pred in enumerate(predictions):
                total_predictions.append({
                    'prediction_date': prediction_dates[i],
                    'total_predicted_consumption': int(pred)
                })
            
            # Plot results for total consumption
            plot_results(time_series, predictions)
            
            # Print summary for total consumption
            print(f"Predicted total consumption (next 30 days):")
            print(f"Minimum: {min(predictions)}")
            print(f"Maximum: {max(predictions)}")
            print(f"Average: {np.mean(predictions):.2f}")
            
        except Exception as e:
            print(f"Error processing total consumption: {e}")
        
        # Process each item
        for item_name in df['item'].unique():
            print(f"\nProcessing predictions for: {item_name}")
            
            try:
                # Prepare data
                X_train, y_train, X_test, y_test, scaler, time_series = prepare_data(df, item_name)
                
                # Build and train model
                model, history = build_lstm_model(X_train, y_train, X_test, y_test)
                
                # Predict next month
                predictions = predict_next_month(model, time_series, scaler)
                
                # Plot results
                plot_results(time_series, predictions, item_name)
                
                # Add to item predictions list
                for i, pred in enumerate(predictions):
                    if i < len(prediction_dates):  # Ensure we have a date for this prediction
                        item_predictions.append({
                            'prediction_date': prediction_dates[i],
                            'item': item_name,
                            'predicted_consumption': int(pred)
                        })
                
                # Print summary of predictions
                print(f"Predicted consumption for {item_name} (next 30 days):")
                print(f"Minimum: {min(predictions)}")
                print(f"Maximum: {max(predictions)}")
                print(f"Average: {np.mean(predictions):.2f}")
            
            except Exception as e:
                print(f"Error processing {item_name}: {e}")
        
        # Create dataframes from the prediction lists
        total_predictions_df = pd.DataFrame(total_predictions)
        item_predictions_df = pd.DataFrame(item_predictions)
        
        # Save predictions to Excel
        if not total_predictions_df.empty or not item_predictions_df.empty:
            with pd.ExcelWriter('consumption_predictions.xlsx', engine='openpyxl') as writer:
                if not total_predictions_df.empty:
                    total_predictions_df.to_excel(writer, sheet_name='Total Predictions', index=False)
                if not item_predictions_df.empty:
                    item_predictions_df.to_excel(writer, sheet_name='Item Predictions', index=False)
            
            print("\nPredictions saved to consumption_predictions.xlsx")
        else:
            print("\nNo predictions were generated.")
    
    except Exception as e:
        print(f"An error occurred: {e}")

if __name__ == "__main__":
    main()