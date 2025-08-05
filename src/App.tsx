import { useCallback, useEffect, useState } from "react";
import "./App.css";
import waitlistImg_1 from "./assets/waitlist-advert/1.png";
import waitlistImg_2 from "./assets/waitlist-advert/2.png";
import waitlistImg_3 from "./assets/waitlist-advert/3.png";
import waitlistImg_4 from "./assets/waitlist-advert/4.png";

import advertImg_1 from "./assets/advert/1.png";
import advertImg_2 from "./assets/advert/2.png";
import advertImg_3 from "./assets/advert/3.png";
import advertImg_4 from "./assets/advert/4.png";

import bonAppetitImg from "./assets/bon-appetit.svg";
import chefHat from "./assets/chef-hat.png";
import deliveryDining from "./assets/delivery-dining.png";
import knifeandPlate from "./assets/knife-and-plate.png";
import restaurantTable from "./assets/restaurant-table.png";

const advertBody = [
  {
    tag: "Restaurant & Food lovers",
    text: "Join our waitlist to be the first to know when we launch!",
    subtext: "Don't miss out on exclusive updates!",
    image: waitlistImg_1,
    alt: "Delicious food",
  },
  {
    tag: "Food lovers",
    text: "Get exclusive updates and offers by joining our waitlist!",
    subtext: "Be the first to know about our delicious offerings!",
    image: waitlistImg_2,
    alt: "Tasty dishes",
  },
  {
    tag: "Restaurant",
    text: "Be part of our community! Join the waitlist for Yummy House.",
    subtext: "Stay updated with our latest news and offers!",
    image: waitlistImg_3,
    alt: "Yummy House logo",
  },
  {
    tag: "Food lovers",
    text: "Don't miss out! Join our waitlist for exciting updates.",
    subtext: "Get ready for a culinary adventure!",
    image: waitlistImg_4,
    alt: "Food lovers",
  },
];

const surveyQuestions = [
  {
    question: "How did you hear about us?",
    options: ["Social Media", "Friend", "Advertisement", "Other"],
    key: 1,
  },
  {
    question: "Are you a restaurant owner or a food lover?",
    options: ["Restaurant Owner", "Food Lover"],
    key: 2,
  },
  {
    question: "What features would you like to see in our app?",
    options: [
      "Online Ordering",
      "Delivery Tracking",
      "Restaurant Reviews",
      "Personalized Recommendations",
      "Other",
    ],
    otherPlaceholder: "Please specify",
    key: 3,
  },
  {
    question: "How often do you order food online?",
    options: ["Daily", "Weekly", "Monthly", "Rarely"],
    key: 4,
  },
];

function App() {
  const [currentAdvert, setCurrentAdvert] = useState(advertBody[0]);
  const [surveyquestion, setSurveyQuestion] = useState<{
    question: string;
    options: string[];
    key: number;
    otherPlaceholder?: string;
  } | null>(null);
  const [openJoinWaitlist, setOpenJoinWaitlist] = useState(false);
  const [openSurvey, setOpenSurvey] = useState(false);
  const [currentQuestionIndex, setCurrentQuestionIndex] = useState(0);
  const [surveyAnswers, setSurveyAnswers] = useState<Record<number, string>>(
    {}
  );
  const [selectedAnswer, setSelectedAnswer] = useState<string>("");
  const [otherAnswer, setOtherAnswer] = useState<string>("");

  useEffect(() => {
    const interval = setInterval(() => {
      setCurrentAdvert((prevAdvert) => {
        const currentIndex = advertBody.indexOf(prevAdvert);
        const nextIndex = (currentIndex + 1) % advertBody.length;
        return advertBody[nextIndex];
      });
    }, 5000);

    return () => clearInterval(interval);
  }, []);

  const JoinWaitlist = useCallback((e: React.FormEvent<HTMLFormElement>) => {
    e.preventDefault();
    setOpenJoinWaitlist(false);
    setOpenSurvey(true);
  }, []);

  return (
    <>
      <header className="flex justify-between items-center-safe me-10 z-50 relative h-32">
        <img src="/logo-1.svg" alt="Yummy House logo" className="logo" />
        <button className="h-12 w-44" onClick={() => setOpenJoinWaitlist(true)}>
          Join Waitlist
        </button>
      </header>
      <main className="">
        {/* Dialog Backdrop */}
        {(openJoinWaitlist || openSurvey) && (
          <div
            className="fixed inset-0 bg-black/50 z-50"
            onClick={() => {
              setOpenJoinWaitlist(false);
              setOpenSurvey(false);
            }}
          ></div>
        )}

        <dialog
          open={openJoinWaitlist}
          className="bg-[var(--secondary-color)] text-white p-6 rounded-lg shadow-lg fixed top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 z-50 w-96"
        >
          <div
            className="absolute top-0 right-2 text-gray-500 hover:text-gray-700 text-2xl cursor-pointer"
            onClick={() => setOpenJoinWaitlist(false)}
          >
            &times;
          </div>
          <h2 className="text-2xl font-bold mb-4">Join Waitlist</h2>
          <p className="mb-4">
            Be the first to know when we launch! Enter your email address below.
          </p>
          <form onSubmit={JoinWaitlist} className="flex flex-col space-y-4">
            <input
              type="email"
              placeholder="Enter your email address"
              className="border rounded w-full h-12 ps-3 rounded-tl-4xl rounded-e-none border-[var(--primary-color)] focus:border-2 focus:bg-black bg-[var(--secondary-color)] focus:outline-none transition-all duration-300 ease-in-out text-white"
            />
            <input
              type="submit"
              className="button-fin text-white p-2 h-12 rounded-br-4xl w-full"
              value={"Join Waitlist"}
            />
          </form>
        </dialog>

        <dialog
          open={openSurvey}
          className="bg-[var(--secondary-color)] text-white p-6 rounded-lg shadow-lg fixed top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 z-50 w-96 max-h-96 overflow-y-auto"
        >
          <div
            className="absolute top-0 right-2 text-gray-500 hover:text-gray-700 text-2xl cursor-pointer"
            onClick={() => {
              setOpenSurvey(false);
              setSurveyQuestion(null);
              setCurrentQuestionIndex(0);
              setSurveyAnswers({});
              setSelectedAnswer("");
              setOtherAnswer("");
            }}
          >
            &times;
          </div>
          <form
            onSubmit={(e) => {
              e.preventDefault();
              if (surveyquestion === null) {
                // Start survey
                setSurveyQuestion(surveyQuestions[0]);
                setCurrentQuestionIndex(0);
              } else {
                // Save current answer
                const answerToSave =
                  selectedAnswer === "Other" && otherAnswer
                    ? otherAnswer
                    : selectedAnswer;
                setSurveyAnswers((prev) => ({
                  ...prev,
                  [surveyquestion.key]: answerToSave,
                }));

                // Check if this is the last question
                if (currentQuestionIndex >= surveyQuestions.length - 1) {
                  // Survey complete
                  console.log("Survey completed:", {
                    ...surveyAnswers,
                    [surveyquestion.key]: answerToSave,
                  });
                  setOpenSurvey(false);
                  setSurveyQuestion(null);
                  setCurrentQuestionIndex(0);
                  setSurveyAnswers({});
                  setSelectedAnswer("");
                  setOtherAnswer("");
                } else {
                  // Go to next question
                  const nextIndex = currentQuestionIndex + 1;
                  setCurrentQuestionIndex(nextIndex);
                  setSurveyQuestion(surveyQuestions[nextIndex]);
                  setSelectedAnswer("");
                  setOtherAnswer("");
                }
              }
            }}
            className="space-y-4"
          >
            {surveyquestion === null ? (
              <div className="space-y-5">
                <h2 className="text-xl font-bold">Quick Survey</h2>
                <div>
                  Would you like to answer some survey questions to help us
                  improve?
                </div>
                <button
                  type="submit"
                  className="button-fin text-white p-2 h-12 rounded-br-4xl w-full"
                >
                  Start Survey
                </button>
                <button
                  type="button"
                  onClick={() => setOpenSurvey(false)}
                  className="bg-gray-600 hover:bg-gray-700 text-white p-2 h-12 rounded w-full"
                >
                  Skip Survey
                </button>
              </div>
            ) : (
              <div className="space-y-4">
                <div className="flex justify-between items-center mb-4">
                  <button
                    type="button"
                    onClick={() => {
                      if (currentQuestionIndex > 0) {
                        const prevIndex = currentQuestionIndex - 1;
                        setCurrentQuestionIndex(prevIndex);
                        setSurveyQuestion(surveyQuestions[prevIndex]);
                        setSelectedAnswer(
                          surveyAnswers[surveyQuestions[prevIndex].key] || ""
                        );
                      } else {
                        setSurveyQuestion(null);
                        setCurrentQuestionIndex(0);
                      }
                    }}
                    className="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded"
                  >
                    Back
                  </button>
                  <span className="text-sm text-gray-400">
                    Question {currentQuestionIndex + 1} of{" "}
                    {surveyQuestions.length}
                  </span>
                </div>

                <h3 className="text-lg font-semibold mb-4">
                  {surveyquestion.question}
                </h3>

                <div className="space-y-2">
                  {surveyquestion.options.map((option, index) => (
                    <label
                      key={index}
                      className="flex items-center space-x-2 cursor-pointer"
                    >
                      <input
                        type="radio"
                        name="survey-option"
                        value={option}
                        checked={selectedAnswer === option}
                        onChange={(e) => setSelectedAnswer(e.target.value)}
                        className="text-orange-500 focus:ring-orange-500"
                      />
                      <span>{option}</span>
                    </label>
                  ))}
                </div>

                {selectedAnswer === "Other" &&
                  surveyquestion.otherPlaceholder && (
                    <input
                      type="text"
                      placeholder={surveyquestion.otherPlaceholder}
                      value={otherAnswer}
                      onChange={(e) => setOtherAnswer(e.target.value)}
                      className="w-full p-2 mt-2 rounded bg-gray-700 text-white border border-gray-600 focus:border-orange-500 focus:outline-none"
                    />
                  )}

                <button
                  type="submit"
                  disabled={
                    !selectedAnswer ||
                    (selectedAnswer === "Other" && !otherAnswer)
                  }
                  className="button-fin text-white p-2 h-12 rounded-br-4xl w-full disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  {currentQuestionIndex >= surveyQuestions.length - 1
                    ? "Submit Survey"
                    : "Next Question"}
                </button>
              </div>
            )}
          </form>
        </dialog>
        <section
          className="p-10 pt-22 space-y-28"
          style={{
            height: "calc(100vh - 8rem)",
          }}
        >
          <div
            key={currentAdvert.tag}
            className="space-y-28 animate-fade-in"
            style={{
              animation: "fadeIn 1.2s ease",
            }}
          >
            <style>
              {`
              @keyframes fadeIn {
                from { opacity: 0; }
                to { opacity: 1; }
              }
              .animate-fade-in {
                animation: fadeIn 1.2s ease;
              }
            `}
            </style>

            <div className="w-full h-full absolute top-0 left-0 z-0 ">
              <img
                className="w-full h-screen object-cover"
                src={currentAdvert.image}
                alt={currentAdvert.alt}
              />
            </div>

            <h1
              className="flex items-end text-3xl mb-8 relative z-10"
              style={{
                fontFamily: "Island Moments, sans-serif",
                color: "#FF6060",
              }}
            >
              {currentAdvert.tag}
              <hr className="w-80 m-4" />
            </h1>
            <p className="text-6xl w-1/2 mb-10 drop-shadow-md drop-shadow-amber-100 relative z-10">
              {currentAdvert.text}
            </p>
            <p className="font-thin text-gray-300 text-3xl drop-shadow-md drop-shadow-amber-100 relative z-10">
              {currentAdvert.subtext}
            </p>
          </div>
          <form
            onSubmit={JoinWaitlist}
            className="flex flex-row items-center w-1/3 h-16 mt-10 relative z-10"
          >
            <input
              type="email"
              placeholder="Enter your email address"
              className="border rounded w-full h-full ps-3 rounded-tl-4xl rounded-e-none border-[var(--primary-color)] focus:border-2 focus:bg-black bg-[var(--secondary-color)] focus:outline-none transition-all duration-300 ease-in-out text-white"
            />
            <input
              type="submit"
              className="button-fin text-white p-2 h-full rounded-br-4xl w-40"
              value={"Join Waitlist"}
            ></input>
          </form>
        </section>

        <section className="grid grid-cols-1 md:grid-cols-2 h-screen top-0">
          <div className="bg-[#130404] flex flex-col items-center justify-center space-y-12 text-center">
            <img
              src={bonAppetitImg}
              alt="Bon Appetit"
              className="drop-shadow-xl hover:scale-110 transition-transform duration-300 hover:drop-shadow-amber-800"
            />
            <div
              className="text-9xl w-1/2 drop-shadow-xl hover:scale-110 transition-transform duration-300 hover:drop-shadow-amber-800"
              style={{
                fontFamily: "Tilt Prism, sans-serif",
                fontWeight: "bold",
              }}
            >
              Tasty To The Soul
            </div>
            <img
              src={chefHat}
              alt="Chef Hat"
              className="drop-shadow-xl hover:scale-110 transition-transform duration-300 hover:drop-shadow-amber-800"
            />
          </div>

          <div className="burger-bg">
            <div className="bg-[#25201A] relative w-5/6 h-5/6 top-1/2 right-0 float-end transform -translate-y-1/2 flex flex-col justify-center items-center space-y-8 p-8">
              <div className="flex flex-row items-center justify-end space-x-8">
                <img src={restaurantTable} alt="Restaurant Table" />
                <span className="space-y-2">
                  <div className="font-bold text-2xl">
                    Taste Meets Convenience
                  </div>
                  <div className="w-11/12">
                    Browse a wide variety of dishes and order your favorites
                    with just a few taps.
                  </div>
                </span>
              </div>

              <div className="flex flex-row items-center justify-end space-x-8">
                <img src={knifeandPlate} alt="Knife and Plate" />
                <span className="space-y-2">
                  <div className="font-bold text-2xl">Dine Like Royalty</div>
                  <div className="w-11/12">
                    Explore top-rated restaurants and enjoy a fine dining
                    experience from the comfort of your home.
                  </div>
                </span>
              </div>

              <div className="flex flex-row items-center justify-end space-x-8">
                <img src={deliveryDining} alt="Delivery Dining" />
                <span className="space-y-2">
                  <div className="font-bold text-2xl">
                    Fast & Reliable Delivery
                  </div>
                  <div className="w-11/12">
                    Get your meals delivered hot and fresh, right to your
                    doorstep anytime, anywhere.
                  </div>
                </span>
              </div>
            </div>
          </div>
        </section>

        <section className="bg-[#1A1A1A] flex h-screen">
          <div className="w-2/12">
            <img
              src={advertImg_1}
              alt="Dish"
              className="h-2/3 w-full object-cover"
            />
            <div className="text-center flex flex-col items-center justify-center h-1/3 space-y-2">
              <div className="font-bold text-2xl">Set Your Table</div>
              <div className="text-md font-thin w-2/3">
                From starters to sides, discover dishes that complete every meal
                beautifully.
              </div>
            </div>
          </div>
          <div className="relative w-4/12">
            <img
              src={advertImg_2}
              alt="Pizza so delicious"
              className="h-full w-full object-cover"
            />
            <div className="absolute bottom-10 p-5 space-y-2">
              <div className="font-bold text-4xl">Pizza so delicious</div>
              <div className="text-xl font-thin">
                Melty cheese, crispy crusts, bold toppings a slice of happiness
                in every bite
              </div>
            </div>
          </div>

          <div className="w-3/12">
            <div className="text-center flex flex-col items-center justify-center h-1/3 space-y-2">
              <div className="font-bold text-2xl">Savor the Flavors</div>
              <div className="text-md font-thin w-2/3">
                Dive into a rich mix of seafood and spices, crafted for true
                food lovers.
              </div>
            </div>
            <img
              src={advertImg_3}
              alt=""
              className="w-full h-2/3 object-cover"
            />
          </div>

          <div className="w-3/12">
            <img
              src={advertImg_4}
              alt=""
              className="h-2/3 w-full object-cover"
            />
            <div className="text-center flex flex-col items-center justify-center h-1/3 space-y-2">
              <div className="font-bold text-2xl">Elevated Bites</div>
              <div className="text-md font-thin w-2/3">
                Perfectly plated brunches and elegant meals because every bite
                should feel special.
              </div>
            </div>
          </div>
        </section>
      </main>
      <footer className="text-white p-4 border-2 border-[var(--primary-color)] bg-[var(--secondary-color)] flex justify-between items-center rounded-bl-4xl rounded-br-4xl">
        <div className="flex items-center space-x-4 underline">
          <img src="/logo.svg" alt="Yummy House Logo" className="h-8" />
          <a
            href="https://www.linkedin.com/in/olajide-adeniji/"
            target="_blank"
            rel="noopener noreferrer"
            className="text-sm font-thin text-orange-500"
          >
            in/olajide-adeniji
          </a>
        </div>
        <div>
          <p>&copy; 2023 Yummy House. All rights reserved.</p>
          <p className="text-sm font-thin">
            Discover, order, and track your favorite meals from local
            restaurants – all in one app.
          </p>
        </div>
      </footer>
    </>
  );
}

export default App;
